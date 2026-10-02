<?php

namespace Tests\Feature\Dashboard;

use App\Models\Experience;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Content\ContentGuard;
use App\Services\Content\SiteTexts;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * One phrase rule for every surface the Owner publishes from (FRAN-097, D-018): the Owner's six forbidden franchise
 * phrases on the franchise page, and the phrases a fact blocks everywhere (the old founding years) on events,
 * announcements and site texts. A draft may hold anything; publishing names the phrase and the field.
 */
class ContentGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    public function test_the_owners_six_franchise_phrases_and_the_rejected_founding_years_are_known(): void
    {
        foreach (['فرصة العمر', 'استثمار مضمون', 'أرباح مضمونة', 'أفضل فرنشايز', 'نجاح مضمون', 'عائد مضمون'] as $phrase) {
            $this->assertSame($phrase, ContentGuard::find("نص فيه {$phrase} هنا.", 'franchise'), $phrase);
        }
        $this->assertSame('best franchise', ContentGuard::find('The Best Franchise in town', 'franchise'), 'case-insensitive, English too');
        $this->assertNull(ContentGuard::find('فرصة العمر', 'about'), 'franchise phrases belong to the franchise page only');
        // D-018: 2018 and "since 2022" are old or wrong — blocked on every surface, read from the Fact Registry.
        $this->assertSame('منذ 2018', ContentGuard::find('شلتر كوفي منذ 2018'));
        $this->assertSame('since 2022', ContentGuard::find('Serving Irbid since 2022', 'events'));
        $this->assertNull(ContentGuard::find('قهوة مختصة في إربد'));
    }

    public function test_an_event_or_announcement_with_a_blocked_phrase_is_not_published_but_a_draft_is_kept(): void
    {
        $event = [
            'title_ar' => 'احتفال شلتر منذ 2018', 'title_en' => 'Coffee Day',
            'body_ar' => 'تذوق مجاني.', 'body_en' => 'Free tasting.',
            'starts_date' => '2026-10-15', 'starts_time' => '18:00', 'ends_date' => '2026-10-16', 'ends_time' => '01:00',
        ];
        $this->post('/dashboard/content/events', $event + ['status' => 'published'])
            ->assertSessionHasErrors(['title_ar' => __('dashboard.blocked_phrase', ['phrase' => 'منذ 2018'])]);
        $this->assertSame(0, Experience::query()->where('type', 'event')->count());
        $this->post('/dashboard/content/events', $event + ['status' => 'draft'])->assertSessionHasNoErrors();
        $this->assertSame(1, Experience::query()->where('type', 'event')->count(), 'a draft may hold it until it is reworded');

        $announcement = [
            'type' => 'announcement', 'placement' => 'top_bar', 'level' => 'auto',
            'title_ar' => 'افتتاح تجريبي', 'title_en' => 'Open since 2022',
            'starts_date' => '2026-10-10', 'starts_time' => '08:00', 'ends_date' => '2026-10-12', 'ends_time' => '23:59',
        ];
        $this->post('/dashboard/content/announcements', $announcement + ['status' => 'published'])->assertSessionHasErrors('title_en');
        $this->post('/dashboard/content/announcements', ['title_en' => 'Sample opening'] + $announcement + ['status' => 'published'])->assertSessionHasNoErrors();
    }

    public function test_a_site_text_with_a_blocked_phrase_is_refused_and_the_site_keeps_its_wording(): void
    {
        $this->put('/dashboard/content/texts/home', ['texts' => ['site.home.lead' => ['ar' => 'شلتر كوفي، تأسست عام 2018.', 'en' => '']]])
            ->assertSessionHasErrors([SiteTexts::fieldId('site.home.lead', 'ar')], null, 'texts-home');
        $this->assertStringNotContainsString('تأسست عام 2018', (string) $this->get('/ar/')->getContent());
    }
}
