<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Award;
use App\Models\Branch;
use App\Models\Experience;
use App\Models\Product;
use App\Models\Recruitment\Application;
use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\UploadSession;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Franchise\FranchiseForm;
use App\Services\Franchise\PartnershipSubmitter;
use App\Services\Franchise\PartnershipValidator;
use App\Services\Media\MediaLibrary;
use App\Services\Recruitment\ApplicationSubmitter;
use App\Services\Recruitment\ApplicationValidator;
use App\Services\Recruitment\AttachmentStore;
use App\Services\Recruitment\CareersForm;
use Carbon\CarbonImmutable;
use Database\Seeders\FranchiseSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Careers\CareersTestHelpers;
use Tests\Feature\Media\MediaLibraryTest;
use Tests\TestCase;

/**
 * Dashboard search (DASH-019): one box over the Owner's data, grouped by area, each result linking to its screen;
 * server-side, Owner only, works without JavaScript, and the words searched for are never stored or logged.
 */
class OwnerSearchTest extends TestCase
{
    use CareersTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
        Storage::fake('media_public');
        $this->seed(MasterDataSeeder::class);
        $this->seed(MenuSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp()]);
    }

    private function product(string $code): int
    {
        return (int) Product::query()->where('code', $code)->value('id');
    }

    private function search(string $query): string
    {
        return (string) $this->get('/dashboard/search?q='.urlencode($query))->assertOk()->getContent();
    }

    /**
     * The result links of one area on the page (empty when the area is absent).
     *
     * @return list<string>
     */
    private function links(string $html, string $group): array
    {
        if (preg_match('#<section class="ui-search-page__group" aria-labelledby="owner-search-'.$group.'">(.*?)</section>#s', $html, $m) !== 1) {
            return [];
        }
        preg_match_all('#class="ui-search-page__link" href="([^"]+)"#', $m[1], $links);

        return $links[1];
    }

    public function test_the_box_is_in_the_header_and_the_results_page_works_without_javascript(): void
    {
        $home = (string) $this->get('/dashboard')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<form class="ui-shell__search" action="'.preg_quote(route('dashboard.search'), '#').'" method="get" role="search">#', $home);
        $this->assertStringContainsString('name="q"', $home);

        $page = (string) $this->get('/dashboard/search')->assertOk()->getContent();
        $this->assertStringNotContainsString('class="ui-shell__search"', $page, 'one search form on its own page');
        $this->assertStringContainsString('id="owner-search"', $page);
        $this->assertStringContainsString('data-owner-search-results', $page);

        $this->assertStringContainsString('اكتب حرفين على الأقل.', $this->search('a'));
        $this->assertSame([], $this->links($this->search('a'), 'menu'));
    }

    public function test_menu_items_by_english_or_arabic_name_or_code_and_sections_and_branches(): void
    {
        $spanish = $this->search('spanish latte');
        $this->assertCount(2, $this->links($spanish, 'menu'));
        $this->assertStringContainsString('المنيو <bdi>(2)</bdi>', $spanish);

        // Arabic letter forms do not matter (ة/ه): the approved Arabic name is found and shown.
        $turkish = $this->search('قهوه تركيه');
        $this->assertContains(route('dashboard.menu.show', $this->product('PRD-00001')), $this->links($turkish, 'menu'));
        $this->assertStringContainsString('قهوة تركية سينجل', $turkish);

        $this->assertSame([route('dashboard.menu.show', $this->product('PRD-00003'))], $this->links($this->search('PRD-00003'), 'menu'));
        $drive = (int) Branch::query()->where('code', 'BR-DRIVE')->value('id');
        $this->assertSame([route('dashboard.branches.show', $drive)], $this->links($this->search('BR-DRIVE'), 'branches'));
        $this->assertNotSame([], $this->links($this->search('شلتر كوفي هاوس'), 'branches'));

        // A wildcard is a character, not "everything".
        $this->assertSame([], $this->links($this->search('%_'), 'menu'));
    }

    public function test_pages_site_texts_events_announcements_team_awards_images_and_employee_of_the_month(): void
    {
        $this->assertSame([route('dashboard.pages.edit', 'franchise')], $this->links($this->search('الفرنشايز'), 'pages'));
        $texts = $this->links($this->search('ستظهر هنا'), 'texts');
        $this->assertContains(route('dashboard.texts.index', ['page' => 'events']).'#f-site-events-empty-text', $texts);

        $event = Experience::query()->create(['type' => 'event', 'title_ar' => 'يوم القهوة التجريبي', 'title_en' => 'Sample coffee day', 'status' => 'draft']);
        $offer = Experience::query()->create(['type' => 'campaign', 'title_ar' => 'عرض تجريبي', 'title_en' => 'Sample offer', 'status' => 'draft']);
        $member = TeamMember::query()->create(['display_name_ar' => 'ليلى', 'display_name_en' => 'Layla', 'job_title_ar' => 'باريستا', 'job_title_en' => 'Barista']);
        $month = Experience::query()->create(['type' => 'recognition', 'title_ar' => 'شكرًا', 'title_en' => 'Thanks', 'status' => 'draft',
            'details' => ['team_member_id' => $member->id, 'month' => '2026-10']]);
        $award = Award::query()->create(['title_ar' => 'جائزة تجريبية', 'title_en' => 'Sample award', 'issuer_ar' => 'جهة', 'issuer_en' => 'Body', 'year' => 2025, 'status' => 'draft']);
        $image = app(MediaLibrary::class)->import(MediaLibraryTest::imageFile(seed: 4), ['source' => 'shelter', 'people_consent' => 'none']);
        $image->forceFill(['alt_ar' => 'واجهة الفرع التجريبية', 'alt_en' => 'Sample branch front'])->save();

        $this->assertSame([route('dashboard.events.edit', $event)], $this->links($this->search('sample coffee'), 'events'));
        $this->assertSame([route('dashboard.announcements.edit', $offer)], $this->links($this->search('عرض تجريبي'), 'announcements'));
        $layla = $this->search('layla');
        $this->assertSame([route('dashboard.team.edit', $member)], $this->links($layla, 'team'));
        $this->assertSame([route('dashboard.recognition.edit', $month)], $this->links($layla, 'recognition'), 'by the person’s name');
        $this->assertSame([route('dashboard.recognition.edit', $month)], $this->links($this->search('تشرين الأول'), 'recognition'), 'by its month');
        $this->assertSame([route('dashboard.awards.edit', $award)], $this->links($this->search('sample award'), 'awards'));
        $this->assertSame([route('dashboard.media.edit', $image)], $this->links($this->search('واجهه الفرع'), 'media'));
        $this->assertSame([route('dashboard.media.edit', $image)], $this->links($this->search($image->code), 'media'));
    }

    public function test_requests_by_reference_number_or_applicant_name_only(): void
    {
        $this->bootCareers();
        app(FranchiseSeeder::class)->run();
        $job = $this->applyJob();
        $partnership = $this->applyPartnership();

        $this->assertSame([route('dashboard.careers.show', $job->id)], $this->links($this->search($job->reference_number), 'careers'));
        $this->assertSame([route('dashboard.careers.show', $job->id)], $this->links($this->search(mb_strtolower($job->reference_number)), 'careers'));
        $this->assertSame([route('dashboard.partnerships.show', $partnership->id)], $this->links($this->search($partnership->reference_number), 'partnerships'));
        $this->assertSame([route('dashboard.careers.show', $job->id)], $this->links($this->search('متقدم تجريبي'), 'careers'));
        $this->assertSame([route('dashboard.partnerships.show', $partnership->id)], $this->links($this->search('Test Partner'), 'partnerships'));

        // Never by email or phone from this box (the requests lists keep their own fuller search).
        foreach (['applicant.test@example.com', '0791234567', 'partner@example.com'] as $private) {
            $html = $this->search($private);
            $this->assertSame([], $this->links($html, 'careers'), $private);
            $this->assertSame([], $this->links($html, 'partnerships'), $private);
        }
    }

    public function test_the_words_searched_for_are_not_stored_or_logged_and_the_page_is_owner_only(): void
    {
        $log = Log::spy();
        $audit = AuditLog::query()->count();
        $this->search('spanish latte');
        $this->search('قهوه');
        $this->assertSame($audit, AuditLog::query()->count(), 'no audit entry');
        $this->assertSame(0, DB::table('search_query_daily')->count(), 'not counted in the visitors’ search log');
        $log->shouldNotHaveReceived('info');
        $log->shouldNotHaveReceived('warning');

        auth()->logout();
        $this->get('/dashboard/search?q=spanish')->assertRedirect(route('login'));
    }

    private function applyJob(): Application
    {
        $result = app(ApplicationValidator::class)->validate($this->validInput(), [$this->cityId()], CarbonImmutable::parse('2026-10-02'));
        $this->assertSame([], $result['errors']);
        $session = UploadSession::query()->create(['expires_at' => now()->addDay()]);
        $cv = app(AttachmentStore::class)->store($session, $this->pdf());
        $this->assertInstanceOf(ApplicationAttachment::class, $cv);
        $consent = app(CareersForm::class)->consent();
        $this->assertNotNull($consent);

        return app(ApplicationSubmitter::class)->submit($result['data'], $session, $cv->id, (string) Str::uuid(), $consent);
    }

    private function applyPartnership(): Application
    {
        $result = app(PartnershipValidator::class)->validate([
            'full_name' => 'Test Partner', 'phone' => '0791234567', 'email' => 'partner@example.com', 'country' => 'JO', 'city' => 'Irbid',
            'market' => 'North', 'partnership_interest_type' => 'single_location', 'experience_band' => 'y3_5', 'experience_text' => '',
            'owns_business' => 'yes', 'location_status' => 'searching', 'introduction' => "Hello.\nA short note.",
            'non_binding_acknowledgement' => '1', 'data_processing_consent' => '1',
        ]);
        $this->assertSame([], $result['errors']);
        $form = app(FranchiseForm::class);
        $ack = $form->acknowledgement();
        $consent = $form->consent();
        $this->assertNotNull($ack);
        $this->assertNotNull($consent);

        return app(PartnershipSubmitter::class)->submit($result['data'], [], 'en', (string) Str::uuid(), $ack, $consent);
    }
}
