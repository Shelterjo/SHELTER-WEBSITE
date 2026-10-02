<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\ContentVersion;
use App\Models\Experience;
use App\Models\Market;
use App\Models\Media;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Dashboard\AnnouncementEditor;
use App\Services\Dashboard\MediaEditor;
use App\Services\Media\MediaLibrary;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Media\MediaLibraryTest;
use Tests\TestCase;

/**
 * CAMP-004 — the campaign / announcement fields the form was missing: the Owner's internal name (the list only), an
 * image chosen only from the approved media library (MEDIA-RIGHTS), and the branches it is for (none = every branch;
 * a page about another branch never shows it). Versions, audit and the blocked-phrase guard stay as they were.
 */
class CampaignFieldsTest extends TestCase
{
    use RefreshDatabase;

    private Branch $drive;

    private Branch $house;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
        $this->drive = Branch::query()->where('slug', 'drive')->firstOrFail();
        $this->house = Branch::query()->where('slug', 'house')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function form(array $overrides = []): array
    {
        return $overrides + [
            'status' => 'published', 'type' => 'campaign', 'placement' => 'top_bar', 'level' => 'auto',
            'title_ar' => 'عرض تجريبي', 'title_en' => 'Sample offer',
            'starts_date' => '2026-10-10', 'starts_time' => '08:00', 'ends_date' => '2026-10-12', 'ends_time' => '23:59',
        ];
    }

    private function page(string $url): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get($url)->assertOk()->getContent();
    }

    private function bar(string $html): ?string
    {
        return preg_match('#<aside[^>]*\bclass="ui-announcement[^"]*"[^>]*>.*?</aside>#s', $html, $m) === 1 ? $m[0] : null;
    }

    private function image(int $seed): Media
    {
        return app(MediaLibrary::class)->import(MediaLibraryTest::imageFile(seed: $seed), ['source' => 'shelter', 'people_consent' => 'none']);
    }

    public function test_the_internal_name_is_for_the_owners_list_only_and_versions_audit_and_the_guard_stay(): void
    {
        $this->get('/dashboard/content/announcements/new')->assertOk()->assertSee('الاسم الداخلي');
        $this->post('/dashboard/content/announcements', $this->form(['internal_name' => str_repeat('ا', 121)]))->assertSessionHasErrors(['internal_name']);
        $this->post('/dashboard/content/announcements', $this->form(['internal_name' => 'حملة الخريف — داخلي', 'title_en' => 'Open since 2022']))
            ->assertSessionHasErrors('title_en');
        $this->post('/dashboard/content/announcements', $this->form(['internal_name' => 'حملة الخريف — داخلي']))->assertSessionHasNoErrors();

        $item = Experience::query()->sole();
        $this->assertSame('حملة الخريف — داخلي', $item->details['internal_name'] ?? null);
        $this->get('/dashboard/content/announcements')->assertOk()->assertSee('حملة الخريف — داخلي')->assertSee('عرض تجريبي');
        $this->get('/dashboard/content/announcements/'.$item->id)->assertOk()->assertSee('value="حملة الخريف — داخلي"', false);
        foreach (['/ar/contact/', '/en/contact/', '/ar/'] as $url) {
            $html = $this->page($url);
            $this->assertStringContainsString($url === '/en/contact/' ? 'Sample offer' : 'عرض تجريبي', (string) $this->bar($html));
            $this->assertStringNotContainsString('حملة الخريف', $html, 'never shown to visitors');
        }
        $this->assertSame(1, ContentVersion::query()->where('versionable_type', $item->getMorphClass())->where('versionable_id', $item->id)->count());
        $this->assertSame(1, AuditLog::query()->where('action', 'announcements.created')->count());
    }

    public function test_only_an_image_the_website_may_show_can_be_chosen_and_the_home_block_draws_it(): void
    {
        Storage::fake('media');
        Storage::fake('media_public');
        $pending = $this->image(5);
        $noWebsite = $this->image(6);
        $noWebsite->forceFill(['approval_status' => Media::APPROVED, 'ok_website' => false, 'alt_ar' => 'صورة', 'alt_en' => 'Image'])->save();
        $approved = $this->image(7);
        $approved->forceFill(['approval_status' => Media::APPROVED, 'ok_website' => true, 'alt_ar' => 'كوب قهوة', 'alt_en' => 'A cup of coffee'])->save();
        app(MediaLibrary::class)->generateVariants($approved);

        // The form offers only the approved image with website rights — nothing else can be picked.
        $form = (string) $this->get('/dashboard/content/announcements/new')->assertOk()->getContent();
        $this->assertStringContainsString('name="media_id" value="'.$approved->id.'"', $form);
        $this->assertStringNotContainsString('name="media_id" value="'.$pending->id.'"', $form);
        $this->assertStringNotContainsString('name="media_id" value="'.$noWebsite->id.'"', $form);
        // A posted id the form never offered is refused, draft or not.
        foreach ([$pending, $noWebsite] as $media) {
            $this->post('/dashboard/content/announcements', $this->form(['media_id' => (string) $media->id]))->assertSessionHasErrors(['media_id']);
            $this->post('/dashboard/content/announcements', $this->form(['media_id' => (string) $media->id, 'status' => 'draft']))->assertSessionHasErrors(['media_id']);
        }
        $this->post('/dashboard/content/announcements', $this->form(['media_id' => '999999']))->assertSessionHasErrors(['media_id']);
        $this->assertSame(0, Experience::query()->count());

        $this->post('/dashboard/content/announcements', $this->form([
            'placement' => 'home_feature', 'media_id' => (string) $approved->id, 'body_ar' => 'سطر تجريبي.', 'body_en' => 'A sample line.',
        ]))->assertSessionHasNoErrors();
        $item = Experience::query()->sole();
        $this->assertSame($approved->id, $item->media_id);
        $home = $this->page('/en/');
        $this->assertStringContainsString('alt="A cup of coffee"', $home);
        $this->assertStringContainsString('class="ui-container ui-feature"', $home);
        $this->assertStringContainsString('alt="كوب قهوة"', $this->page('/ar/'), 'the alternative text in the page language');
        app()->setLocale('en');
        $this->assertContains('Announcement or offer: Sample offer', app(MediaEditor::class)->usedIn($approved, 'en'), 'the media library says where it is used');
        $this->get('/dashboard/content/announcements/'.$item->id)->assertOk()->assertSee('alt="كوب قهوة"', false);

        // The top bar is one line of text: never an image.
        $item->forceFill(['placements' => ['top_bar']])->save();
        $this->assertStringNotContainsString('<img', (string) $this->bar($this->page('/en/contact/')));

        // Rights withdrawn: the home block loses the image at once, and it cannot be chosen again.
        $item->forceFill(['placements' => ['home_feature']])->save();
        $approved->forceFill(['ok_website' => false])->save();
        $home = $this->page('/en/');
        $this->assertStringContainsString('Sample offer', $home);
        $this->assertStringNotContainsString('alt="A cup of coffee"', $home);
        $this->put('/dashboard/content/announcements/'.$item->id, $this->form(['placement' => 'home_feature', 'media_id' => (string) $approved->id]))
            ->assertSessionHasErrors(['media_id']);
    }

    public function test_a_campaign_for_one_branch_stays_off_the_other_branchs_pages(): void
    {
        $this->post('/dashboard/content/announcements', $this->form(['branches' => ['999999']]))->assertSessionHasErrors(['branches']);
        $this->post('/dashboard/content/announcements', $this->form(['branches' => [(string) $this->drive->id], 'internal_name' => 'للدرايف فقط']))->assertSessionHasNoErrors();
        $driveOnly = Experience::query()->sole();
        $this->assertSame([$this->drive->id], $driveOnly->branch_ids);

        $this->assertStringContainsString('عرض تجريبي', (string) $this->bar($this->page('/ar/jo/locations/irbid/drive/')), 'its own branch');
        $this->assertStringContainsString('عرض تجريبي', (string) $this->bar($this->page('/ar/jo/menu/?branch=drive')), 'its own branch’s menu');
        $this->assertStringContainsString('عرض تجريبي', (string) $this->bar($this->page('/ar/contact/')), 'pages about no branch in particular');
        $this->assertStringContainsString('عرض تجريبي', (string) $this->bar($this->page('/ar/jo/menu/')));
        $this->assertNull($this->bar($this->page('/ar/jo/locations/irbid/house/')), 'never on the other branch’s page');
        $this->assertNull($this->bar($this->page('/ar/jo/menu/?branch=house')), 'nor on its menu');
        $this->get('/dashboard/content/announcements')->assertSee('فقط في:');

        // The other branch's page takes the next one meant for it (an announcement for every branch, lower priority).
        $this->post('/dashboard/content/announcements', $this->form(['type' => 'announcement', 'title_ar' => 'إعلان للجميع', 'title_en' => 'For everyone']))->assertSessionHasNoErrors();
        $this->assertStringContainsString('إعلان للجميع', (string) $this->bar($this->page('/ar/jo/locations/irbid/house/')));
        $this->assertStringContainsString('عرض تجريبي', (string) $this->bar($this->page('/ar/jo/locations/irbid/drive/')), 'the campaign still wins on its branch');

        // A higher one for HOUSE only wins the pages about no branch, yet DRIVE's own one still shows on DRIVE's page.
        $this->post('/dashboard/content/announcements', $this->form(['title_ar' => 'عرض الهاوس', 'title_en' => 'House offer', 'level' => 'urgent', 'branches' => [(string) $this->house->id]]))->assertSessionHasNoErrors();
        $this->assertStringContainsString('عرض الهاوس', (string) $this->bar($this->page('/ar/contact/')));
        $this->assertStringContainsString('عرض تجريبي', (string) $this->bar($this->page('/ar/jo/locations/irbid/drive/')));
        $this->assertSame('live', app(AnnouncementEditor::class)->state($driveOnly->refresh(), Market::query()->firstOrFail()), 'live where it shows');

        // No branch chosen = every branch.
        $this->put('/dashboard/content/announcements/'.$driveOnly->id, $this->form(['level' => 'urgent', 'title_ar' => 'عرض للكل', 'title_en' => 'Offer for all']))->assertSessionHasNoErrors();
        $this->assertNull($driveOnly->refresh()->branch_ids);
        $this->assertStringContainsString('عرض للكل', (string) $this->bar($this->page('/ar/jo/locations/irbid/drive/')));
        $this->assertSame(2, ContentVersion::query()->where('versionable_type', $driveOnly->getMorphClass())->where('versionable_id', $driveOnly->id)->count(), 'every save keeps a version');
    }
}
