<?php

namespace Tests\Feature\Site;

use App\Enums\FactSource;
use App\Enums\FactStatus;
use App\Enums\PublishStatus;
use App\Models\Award;
use App\Models\Branch;
use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\Page;
use App\Models\TeamMember;
use App\Services\MasterData\FactRegistry;
use App\Services\Media\MediaLibrary;
use Database\Seeders\FranchiseSeeder;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Media\MediaLibraryTest;
use Tests\TestCase;

/** SI-B13 Media Center + Press Kit, SI-B14 Awards, SI-B15 SHELTER Family — approved, verified, consented content only. */
class ShowcasePagesTest extends TestCase
{
    use RefreshDatabase;

    private int $images = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        Storage::fake('media');
        Storage::fake('media_public');
    }

    /** @return TestResponse<Response> */
    private function fresh(string $url): TestResponse
    {
        $this->app->forgetScopedInstances();

        return $this->get($url);
    }

    /** @param  array<string, mixed>  $attributes */
    private function usableImage(array $attributes = []): Media
    {
        $library = app(MediaLibrary::class);
        $media = $library->import(MediaLibraryTest::imageFile(seed: ++$this->images), ['source' => 'shelter', 'people_consent' => 'none', 'alt_ar' => 'صورة اختبار', 'alt_en' => 'Test photo']);
        $media->forceFill($attributes + ['approval_status' => Media::APPROVED, 'approved_at' => now(), 'ok_website' => true])->save();
        $library->generateVariants($media->refresh());

        return $media->refresh();
    }

    private function award(string $title, int $year, bool $verified = true): Award
    {
        $award = Award::query()->create([
            'title_ar' => 'جائزة '.$title, 'title_en' => 'Award '.$title, 'issuer_ar' => 'جهة', 'issuer_en' => 'Issuer', 'year' => $year,
            'evidence_url' => 'https://example.com/'.$title, 'status' => 'published',
        ]);
        if ($verified) {
            app(FactRegistry::class)->register($award->factKey(), 'awards', $award->factValue(), FactStatus::Approved, FactSource::OwnerDecision, decisionRef: 'D-TEST');
        }

        return $award;
    }

    /** @param  array<string, mixed>  $attributes */
    private function member(string $name, array $attributes = []): TeamMember
    {
        return TeamMember::query()->create($attributes + [
            'display_name_ar' => 'اسم '.$name, 'display_name_en' => 'Name '.$name, 'job_title_ar' => 'باريستا', 'job_title_en' => 'Barista',
            'branch_id' => Branch::query()->where('slug', 'drive')->value('id'), 'is_published' => true,
            'publish_consent_at' => now(), 'publish_consent_version' => 'family-consent-v1',
        ]);
    }

    public function test_nothing_shows_until_there_is_approved_content(): void
    {
        foreach (['/ar/media/', '/en/media/', '/ar/awards/', '/ar/family/'] as $url) {
            $this->fresh($url)->assertNotFound();
        }
        $this->award('draft', 2025, verified: false);
        $this->member('no-consent', ['publish_consent_at' => null]);
        $this->member('unpublished', ['is_published' => false]);
        $this->member('withdrawn', ['consent_withdrawn_at' => now()]);
        $this->fresh('/ar/awards/')->assertNotFound();
        $this->fresh('/ar/family/')->assertNotFound();
        $footer = (string) $this->fresh('/en/')->getContent();
        foreach (['/en/media/', '/en/awards/', '/en/family/'] as $url) {
            $this->assertStringNotContainsString($url, $footer);
        }
    }

    public function test_awards_show_only_verified_unchanged_records(): void
    {
        $this->award('old', 2024);
        $latest = $this->award('new', 2026);
        $this->award('unverified', 2025, verified: false);

        $html = (string) $this->fresh('/en/awards/')->assertOk()->getContent();
        $this->assertLessThan(strpos($html, 'Award old'), strpos($html, 'Award new'), 'newest first');
        $this->assertStringNotContainsString('Award unverified', $html);
        $this->assertStringContainsString('href="https://example.com/new"', $html);
        $this->assertStringNotContainsString('"@type":"Review"', $html);
        $this->assertMatchesRegularExpression('#href="http://localhost/en/awards/"\s*>Awards &amp; recognition</a>#', (string) $this->fresh('/en/')->getContent());

        // An edit after approval takes it offline until the Owner approves again.
        $latest->update(['year' => 2020]);
        $this->assertStringNotContainsString('Award new', (string) $this->fresh('/en/awards/')->getContent());
    }

    public function test_family_shows_consenting_members_as_they_agreed(): void
    {
        $consented = $this->usableImage(['people_consent' => 'recorded', 'people_consents' => [['person' => 'Name a', 'consented_at' => '2026-10-01', 'scopes' => ['website']]]]);
        $noConsentPhoto = $this->usableImage(['people_consent' => 'not_recorded']);
        $this->member('a', ['photo_media_id' => $consented->id, 'join_date' => '2023-05-01', 'show_join_date' => true, 'bio_en' => 'Loves V60.', 'bio_ar' => 'يحب V60.', 'show_bio' => true, 'sort_order' => 1]);
        $this->member('b', ['photo_media_id' => $noConsentPhoto->id, 'bio_en' => 'Hidden bio.', 'bio_ar' => 'نبذة مخفية.', 'sort_order' => 2]);
        $this->member('c', ['publish_consent_at' => null]);

        $html = (string) $this->fresh('/en/family/')->assertOk()->getContent();
        $this->assertStringContainsString('Name a', $html);
        $this->assertStringContainsString('With us since May 2023', $html);
        $this->assertStringContainsString('Loves V60.', $html);
        $this->assertStringContainsString('SHELTER COFFEE DRIVE', $html);
        $this->assertSame(1, substr_count($html, '<picture class="ui-picture'), 'only the photo with recorded consent');
        $this->assertStringContainsString('alt="Test photo"', $html);
        $this->assertStringContainsString('<span class="ui-team__initial" aria-hidden="true">N</span>', $html, 'b shows its initial');
        $this->assertStringNotContainsString('Hidden bio.', $html, 'bio only when switched on');
        $this->assertStringNotContainsString('Name c', $html);
        $this->assertStringNotContainsString('"@type":"Person"', $html);
        $this->assertStringContainsString('href="http://localhost/en/family/"', (string) $this->fresh('/en/')->getContent());
    }

    public function test_the_media_center_shows_curated_approved_items_only(): void
    {
        $page = Page::query()->create(['key' => 'media', 'type' => 'brand', 'title_ar' => 'المركز الإعلامي', 'title_en' => 'Media Center',
            'status' => PublishStatus::Published, 'published_at' => now()->subMinute()]);
        $page->sections()->create(['type' => 'text', 'heading_ar' => 'عنّا', 'heading_en' => 'About', 'body_ar' => 'نص.', 'body_en' => 'Text.']);
        $press = $this->usableImage();
        $pending = $this->usableImage(['approval_status' => 'PENDING OWNER APPROVAL']);
        foreach ([$press, $pending] as $sort => $media) {
            MediaUsage::query()->create(['media_id' => $media->id, 'usable_type' => Page::class, 'usable_id' => $page->id, 'slot' => 'press_kit', 'sort' => $sort]);
        }
        $this->award('press', 2026);

        $html = (string) $this->fresh('/en/media/')->assertOk()->getContent();
        foreach (['Official name', 'SHELTER COFFEE', 'شلتر كوفي', 'Founded', '2019', 'SHELTER COFFEE DRIVE', 'Press photos', 'Award press', 'href="http://localhost/en/awards/"'] as $part) {
            $this->assertStringContainsString($part, $html);
        }
        $this->assertSame(1, substr_count($html, '<picture class="ui-picture"'), 'MR-T09: a pending asset is absent');
        foreach (['@shelterjo.com', 'mailto:', 'download'] as $absent) {
            $this->assertStringNotContainsString($absent, $html, 'no press contact or logo download before PO-062');
        }
        $this->assertMatchesRegularExpression('#href="http://localhost/en/media/"\s*>Media Center</a>#', (string) $this->fresh('/en/')->getContent());
    }

    public function test_the_sitemap_lists_published_pages_only(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config(['shelter.indexing' => true]);
        $xml = (string) $this->fresh('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringNotContainsString('/franchise/', $xml);
        $this->assertStringContainsString('/ar/careers/', $xml);

        app(FranchiseSeeder::class)->run(); // directly: a production-mode db:seed would stop to ask for confirmation
        $this->award('sitemap', 2026);
        $xml = (string) $this->fresh('/sitemap.xml')->getContent();
        $this->assertStringContainsString('<loc>http://localhost/ar/franchise/</loc>', $xml);
        $this->assertStringContainsString('<loc>http://localhost/en/awards/</loc>', $xml);
        $this->assertStringNotContainsString('/family/', $xml);
        $this->assertStringNotContainsString('/feedback/', $xml);
    }
}
