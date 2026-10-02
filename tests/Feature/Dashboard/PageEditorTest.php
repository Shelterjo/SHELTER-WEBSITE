<?php

namespace Tests\Feature\Dashboard;

use App\Enums\PublishStatus;
use App\Models\ContentVersion;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use Database\Seeders\FranchiseSeeder;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/** Dashboard → Content → Pages (M30, M50): the Owner edits every brand page without code, under the site's own rules. */
class PageEditorTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp()]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return TestResponse<Response>
     */
    private function save(string $key, array $data): TestResponse
    {
        return $this->put('/dashboard/content/pages/'.$key, $data);
    }

    /** @return array<string, mixed> */
    private function aboutPage(string $status = 'published'): array
    {
        return [
            'status' => $status, 'title_ar' => 'من نحن', 'title_en' => 'About us', 'description_ar' => 'وصف.', 'description_en' => 'Description.',
            'sections' => [
                ['type' => 'text', 'heading_ar' => 'قصتنا', 'heading_en' => 'Our story', 'body_ar' => 'فقرة أولى.', 'body_en' => 'First paragraph.', 'visible' => '1', 'sort' => '1'],
                ['type' => 'list', 'heading_ar' => '', 'heading_en' => '', 'body_ar' => "أ\nب", 'body_en' => "A\nB", 'visible' => '1', 'sort' => '2'],
            ],
        ];
    }

    private function fresh(string $url): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get($url)->getContent();
    }

    public function test_the_dashboard_speaks_arabic_and_lists_every_page_with_its_state(): void
    {
        $this->seed(FranchiseSeeder::class);
        $home = (string) $this->get('/dashboard')->assertOk()->getContent();
        $this->assertStringContainsString('مركز التحكم', $home);
        $this->assertStringContainsString('صفحات غير منشورة', $home);
        $this->assertStringContainsString('href="http://localhost/dashboard/content/pages"', $home);

        $list = (string) $this->get('/dashboard/content/pages')->assertOk()->getContent();
        foreach (['من نحن', 'الأسئلة الشائعة', 'الفرنشايز والشراكات', 'المركز الإعلامي', 'سياسة الخصوصية', 'الشروط والأحكام'] as $label) {
            $this->assertStringContainsString($label, $list);
        }
        $this->assertStringContainsString('منشورة على الموقع', $list, 'franchise is live');
        $this->assertStringContainsString('لم تُكتب بعد', $list);
        $this->get('/dashboard/content/pages/blog')->assertNotFound();
    }

    public function test_a_draft_is_saved_with_a_version_and_stays_off_the_site(): void
    {
        $this->save('about', array_merge($this->aboutPage('draft'), ['sections' => [['type' => 'text', 'body_ar' => 'نص عربي فقط.', 'visible' => '1']]]))
            ->assertRedirect('/dashboard/content/pages/about')->assertSessionHas('status', 'تم الحفظ.');

        $page = Page::query()->where('key', 'about')->sole();
        $this->assertSame(PublishStatus::Draft, $page->status);
        $this->assertSame('owner', $page->origin);
        $this->assertSame(1, $page->sections()->count(), 'an incomplete section may wait in a draft');
        $this->assertSame(1, ContentVersion::query()->where('versionable_type', Page::class)->where('versionable_id', $page->id)->count());
        $this->assertTrue(DB::table('audit_logs')->where('action', 'pages.saved')->exists());
        $this->app->forgetScopedInstances();
        $this->get('/ar/about/')->assertNotFound();
    }

    public function test_publishing_is_refused_with_the_exact_reason_and_nothing_changes(): void
    {
        $data = $this->aboutPage();
        $data['sections'][1]['body_en'] = '';
        $data['title_en'] = '';
        $this->save('about', $data)->assertRedirect()->assertSessionHasErrors(['title_en', 'sections.1']);
        $this->assertSame(0, Page::query()->count());

        $html = (string) $this->from('/dashboard/content/pages/about')->followingRedirects()->save('about', $data)->getContent();
        $this->assertStringContainsString('القسم 2: النص الإنجليزي فارغ.', $html);
        $this->assertStringContainsString('العنوان مطلوب باللغتين لنشر الصفحة.', $html);
        $this->assertStringContainsString('href="#section-1"', $html, 'the summary links to the section');
    }

    public function test_a_published_page_goes_live_and_joins_search(): void
    {
        $this->save('about', $this->aboutPage())->assertRedirect('/dashboard/content/pages/about');

        $html = $this->fresh('/en/about/');
        $this->assertStringContainsString('About us', $html);
        $this->assertStringContainsString('First paragraph.', $html);
        $this->assertStringContainsString('About us', $this->fresh('/en/search/?q=story'));
        $this->assertNotNull(Page::query()->where('key', 'about')->value('published_at'));
    }

    public function test_sections_are_reordered_and_removing_one_archives_it(): void
    {
        $this->save('about', $this->aboutPage());
        $ids = PageSection::query()->orderBy('sort')->pluck('id')->all();
        $data = $this->aboutPage();
        $data['sections'][0]['id'] = (string) $ids[0];
        $data['sections'][1]['id'] = (string) $ids[1];
        $data['sections'][0]['sort'] = '2';
        $data['sections'][1]['sort'] = '1';
        $this->save('about', $data)->assertRedirect();
        $this->assertSame(['list', 'text'], PageSection::query()->whereNull('archived_at')->orderBy('sort')->pluck('type')->all());

        $data['sections'][1]['remove'] = '1';
        $this->save('about', $data)->assertRedirect();
        $removed = PageSection::query()->whereKey($ids[1])->first();
        $this->assertNotNull($removed?->archived_at, 'archived, never hard-deleted');
        $this->assertStringNotContainsString('<ul', (string) preg_replace('#<(header|footer).*?</\1>#s', '', $this->fresh('/en/about/')));
        $this->assertSame(3, ContentVersion::query()->where('versionable_type', Page::class)->count(), 'every save is a version');
    }

    public function test_a_blank_new_section_is_ignored_and_a_blocked_claim_is_refused(): void
    {
        $data = $this->aboutPage();
        $data['sections'][] = ['type' => 'text', 'heading_ar' => '', 'heading_en' => '', 'body_ar' => '', 'body_en' => '', 'visible' => '1'];
        $this->save('about', $data)->assertRedirect('/dashboard/content/pages/about');
        $this->assertSame(2, PageSection::query()->count());

        $this->seed(FranchiseSeeder::class);
        $franchise = $this->get('/dashboard/content/pages/franchise')->assertOk();
        $franchise->assertSee('كن شريكًا في نمو', false);
        $this->save('franchise', ['status' => 'published', 'title_ar' => 'عنوان', 'title_en' => 'Title',
            'sections' => [['type' => 'text', 'body_ar' => 'أرباح مضمونة للجميع.', 'body_en' => 'Text.', 'visible' => '1']]])
            ->assertSessionHasErrors('blocked');
        $this->assertSame('كن شريكًا مع SHELTER COFFEE', Page::query()->where('key', 'franchise')->value('name_ar'), 'untouched');
    }

    public function test_a_refused_save_keeps_every_card_as_sent_and_links_each_error_to_its_card(): void
    {
        // Cards in screen order 3, 0, 5 (a new card moved up): every one comes back with what was typed, unticked stays
        // unticked, and the error names the card's number on screen and links to that card.
        $data = $this->aboutPage();
        $data['sections'] = [
            3 => ['type' => 'text', 'heading_ar' => 'جديد أول', 'heading_en' => 'First new', 'body_ar' => 'نص.', 'body_en' => 'Text.', 'visible' => '1', 'sort' => '1'],
            0 => ['type' => 'text', 'heading_ar' => 'مخفي', 'heading_en' => '', 'body_ar' => 'نص مخفي.', 'body_en' => '', 'sort' => '2'],
            5 => ['type' => 'text', 'heading_ar' => 'جديد ثان', 'heading_en' => '', 'body_ar' => 'نص فقط بالعربية.', 'body_en' => '', 'visible' => '1', 'sort' => '3'],
        ];
        $html = (string) $this->from('/dashboard/content/pages/about')->followingRedirects()->save('about', $data)->getContent();
        $this->assertNull(Page::query()->where('key', 'about')->first(), 'nothing saved');
        $this->assertSame(['3', '0', '5', '6'], array_map(fn (array $m): string => $m[1], $this->allMatches('/id="section-(\d+)" data-section/', $html)), 'as sent, then one blank card');
        foreach (['جديد أول', 'مخفي', 'جديد ثان'] as $typed) {
            $this->assertStringContainsString($typed, $html);
        }
        $this->assertMatchesRegularExpression('/id="s0-visible"(?![^>]*checked)[^>]*>/', $html, 'unticked stays unticked');
        $this->assertMatchesRegularExpression('/id="s6-visible"[^>]*checked/', $html, 'the blank card defaults to visible');
        $this->assertStringContainsString('href="#section-5"', $html);
        $this->assertStringContainsString(e(__('dashboard.pages.errors.section', ['n' => 3, 'problem' => __('dashboard.pages.problems.body_en')])), $html);
    }

    /** @return list<array<int, string>> */
    private function allMatches(string $pattern, string $subject): array
    {
        preg_match_all($pattern, $subject, $found, PREG_SET_ORDER);

        return $found;
    }
}
