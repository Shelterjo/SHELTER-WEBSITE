<?php

namespace Tests\Feature\Dashboard;

use App\Models\Award;
use App\Models\Branch;
use App\Models\Feedback;
use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\UploadSession;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Recruitment\ApplicationSubmitter;
use App\Services\Recruitment\ApplicationValidator;
use App\Services\Recruitment\AttachmentStore;
use App\Services\Recruitment\CareersForm;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Careers\CareersTestHelpers;
use Tests\Feature\DesignSystem\RendersComponents;
use Tests\TestCase;

/**
 * UX-006 dashboard polish (docs/qa/DESIGN-REVIEW-2026-10-02.md, DR-15 … DR-56): the "All sections" sheet on phones,
 * one primary button per screen, full-size action links, one empty-state pattern and the sign-in brand line — the
 * markup the stylesheets and the browser check rely on.
 */
class DashboardPolishTest extends TestCase
{
    use CareersTestHelpers;
    use RefreshDatabase;
    use RendersComponents;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        // Texts and Settings ask for a recent re-confirmation, as in SiteTextsTest.
        $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    private function html(string $url): string
    {
        return (string) $this->get($url)->assertOk()->getContent();
    }

    private function page(string $url): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true); // HTML5 elements (dialog, nav, bdi) are unknown to libxml
        $document->loadHTML('<?xml encoding="utf-8"?>'.$this->html($url));
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    /** @return list<string> */
    private function texts(DOMXPath $xpath, string $query): array
    {
        $out = [];
        foreach ($xpath->query($query) ?: [] as $node) {
            $out[] = trim($node->nodeValue ?? '');
        }

        return $out;
    }

    /** The first group of a pattern that must match exactly once. */
    private function capture(string $pattern, string $html): string
    {
        $this->assertSame(1, preg_match($pattern, $html, $match), $pattern);

        return $match[1] ?? '';
    }

    private function primaries(string $html): int
    {
        return substr_count($html, 'ui-button--primary');
    }

    /** A submit button with exactly these classes (attribute order depends on the attributes passed), then its words. */
    private function submit(string $classes, string $label = ''): string
    {
        $c = preg_quote($classes, '/');

        return '/<button (?:type="submit" class="'.$c.'"|class="'.$c.'" type="submit")>'.($label === '' ? '' : '\s*'.preg_quote($label, '/')).'/u';
    }

    public function test_phones_get_an_all_sections_sheet_with_the_same_groups_as_the_side_column(): void
    {
        $xpath = $this->page('/dashboard/live');

        // DR-18: the row is its own scroller; the button stays outside it and opens the sheet.
        $this->assertSame(1, $this->countMatches($xpath, '//nav['.self::cls('ui-shell__nav').']/div['.self::cls('ui-shell__nav-scroll').' and @data-ui-nav-strip]'));
        $button = $this->element($xpath, '//nav['.self::cls('ui-shell__nav').']//div['.self::cls('ui-shell__nav-more').']/button');
        $this->assertSame('dashboard-sections', $button->getAttribute('commandfor'));
        $this->assertSame('show-modal', $button->getAttribute('command'));
        $this->assertSame('dashboard-sections', $button->getAttribute('aria-controls'));
        $this->assertSame('false', $button->getAttribute('aria-expanded'));
        $this->assertSame('كل الأقسام', trim($button->textContent), 'the name is the visible words (icon only on small phones, words kept for screen readers)');

        $sheet = '//dialog[@id="dashboard-sections"]';
        $this->assertSame('كل الأقسام', trim($this->element($xpath, $sheet.'//h2')->textContent));
        $this->assertSame('dashboard-sections-title', $this->element($xpath, $sheet.'//nav')->getAttribute('aria-labelledby'));

        // The same destinations, in the same order and groups, from the same composer (DashboardChrome).
        $row = $this->texts($xpath, '//div[@data-ui-nav-strip]//a['.self::cls('ui-sidebar__link').']/@href');
        $this->assertGreaterThan(15, count($row));
        $this->assertSame($row, $this->texts($xpath, $sheet.'//a['.self::cls('ui-sidebar__link').']/@href'));
        $this->assertSame(['المحتوى', 'الطلبات', 'البيانات'], $this->texts($xpath, $sheet.'//h3['.self::cls('ui-shell__sheet-group').']'));
        $this->assertSame($this->texts($xpath, '//div[@data-ui-nav-strip]/p['.self::cls('ui-shell__nav-group').']'), $this->texts($xpath, $sheet.'//h3'));
        $this->assertSame(['النشط الآن'], $this->texts($xpath, $sheet.'//a[@aria-current="page"]'), 'the current screen is marked in the sheet too');
        $this->assertSame(['النشط الآن'], $this->texts($xpath, '//div[@data-ui-nav-strip]//a[@aria-current="page"]'));
    }

    public function test_the_sign_in_brand_line_follows_the_page_and_isolates_only_the_latin_name(): void
    {
        Auth::logout();
        $brand = '<p class="ui-auth__brand"><bdi lang="en" dir="ltr">SHELTER COFFEE</bdi></p>';
        $this->assertStringContainsString($brand, $this->html('/dashboard/login'), 'DR-20: no dir="ltr" on the line itself');

        $this->post('/dashboard/login', ['email' => $this->owner->email, 'password' => 'correct horse battery staple'])->assertRedirect('/dashboard/two-factor');
        $this->assertStringContainsString($brand, $this->html('/dashboard/two-factor'));
    }

    public function test_row_and_card_actions_are_secondary_so_each_screen_keeps_one_primary(): void
    {
        $branch = (int) Branch::query()->value('id');
        Feedback::query()->create([
            'branch_id' => $branch, 'rating_overall' => 4, 'comment' => 'Test comment', 'locale' => 'ar', 'entry_point' => 'direct',
            'idempotency_key' => (string) Str::uuid(), 'form_version' => 'feedback-form-v1', 'submitted_at' => now()->subDay(),
        ]);
        Award::query()->create(['title_ar' => 'جائزة اختبار', 'year' => 2025, 'status' => 'draft']);
        $this->post('/dashboard/content/events', ['status' => 'draft', 'title_ar' => 'فكرة فعالية'])->assertSessionHasNoErrors();

        // DR-19: the four screens of the review — no primary at all now (each save or edit belongs to its own card).
        foreach (['/dashboard/content/pages', '/dashboard/content/texts', '/dashboard/requests/feedback', '/dashboard/data/contacts', '/dashboard/data/branches'] as $url) {
            $this->assertSame(0, $this->primaries($this->html($url)), $url);
        }
        $pages = $this->html('/dashboard/content/pages');
        $this->assertGreaterThan(1, preg_match_all('/<a href="[^"]*\/dashboard\/content\/pages\/[a-z-]+" class="ui-button ui-button--secondary ui-button--sm">/', $pages));
        $this->assertMatchesRegularExpression($this->submit('ui-button ui-button--secondary ui-button--sm', (string) __('dashboard.requests.feedback.redact_save')), $this->html('/dashboard/requests/feedback'));
        $this->assertGreaterThan(1, preg_match_all($this->submit('ui-button ui-button--secondary', (string) __('dashboard.contacts.save')), $this->html('/dashboard/data/contacts')));

        // Screens with one main action keep exactly that one: "New item", "Add an event", "Add an award", "Save".
        foreach (['/dashboard/data/menu', '/dashboard/content/events', '/dashboard/content/awards', '/dashboard/settings'] as $url) {
            $this->assertSame(1, $this->primaries($this->html($url)), $url);
        }
        $this->assertMatchesRegularExpression('/<a href="[^"]*\/dashboard\/content\/awards\/\d+" class="ui-button ui-button--secondary ui-button--sm">/', $this->html('/dashboard/content/awards'));
    }

    public function test_standalone_text_links_are_full_size_touch_targets(): void
    {
        // DR-22: the item names and "Review the Arabic names" in the menu, the page names in Google visibility,
        // and every row link in Active now (season included).
        $menu = $this->html('/dashboard/data/menu');
        $this->assertMatchesRegularExpression('/<a class="ui-action-link" href="[^"]*\/dashboard\/data\/menu\/review\/[^"]*">'.preg_quote(__('dashboard.menu.review_names'), '/').'<\/a>/u', $menu);
        $this->assertGreaterThan(1, preg_match_all('/<a class="ui-action-link" href="[^"]*\/dashboard\/data\/menu\/\d+" lang="en" dir="ltr">/', $menu));

        $seo = $this->html('/dashboard/seo');
        $this->assertGreaterThan(6, preg_match_all('/<a class="ui-action-link" href="[^"]*\/dashboard\/content\/texts\?page=/', $seo));

        $live = $this->html('/dashboard/live');
        $this->assertMatchesRegularExpression('/<a class="ui-action-link" href="[^"]*\/dashboard\/data\/menu\/season#season-\d+">'.preg_quote(__('dashboard.live.season_manage'), '/').'<\/a>/u', $live);
        $this->assertDoesNotMatchRegularExpression('/<p class="ui-record__status">\s*(<[^>]+>\s*)*<a href=/', $live, 'no bare status-line link left');
    }

    public function test_empty_states_share_one_pattern_with_a_secondary_next_step(): void
    {
        // DR-56: title · what it means · the next step; DR-19: the step is secondary (the header keeps the primary).
        $awards = $this->html('/dashboard/content/awards');
        $this->assertStringContainsString(__('dashboard.awards.empty_hint'), $awards);
        $this->assertMatchesRegularExpression('/<div class="ui-cluster ui-empty-state__actions"><a href="[^"]*\/dashboard\/content\/awards\/new" class="ui-button ui-button--secondary">/', $awards);
        $archived = $this->html('/dashboard/content/awards?show=archived');
        $this->assertStringNotContainsString(__('dashboard.awards.empty_hint'), $archived, 'the archive view only says it is empty');
        $this->assertStringNotContainsString('ui-empty-state__actions', $archived);

        $team = $this->html('/dashboard/content/team');
        $this->assertStringContainsString(__('dashboard.team.empty_hint'), $team);
        $this->assertMatchesRegularExpression('/<div class="ui-cluster ui-empty-state__actions"><a href="[^"]*\/dashboard\/content\/team\/new" class="ui-button ui-button--secondary">/', $team);

        foreach (['events' => 'events/new', 'announcements' => 'announcements/new', 'media' => 'media/upload'] as $screen => $create) {
            $html = $this->html('/dashboard/content/'.$screen);
            $this->assertMatchesRegularExpression('/<div class="ui-cluster ui-empty-state__actions"><a href="[^"]*\/dashboard\/content\/'.preg_quote($create, '/').'" class="ui-button ui-button--secondary">/', $html, $screen);
            $this->assertSame(1, $this->primaries($html), "{$screen}: the header action stays the only primary");
        }
    }

    public function test_careers_shows_one_line_for_an_empty_period_and_the_cards_otherwise(): void
    {
        // DR-50: eight zero cards pushed the list below the fold on phones.
        $empty = $this->html('/dashboard/requests/careers');
        $this->assertStringContainsString('<p class="ui-inbox-summary">لا توجد طلبات توظيف في «هذا الشهر».</p>', $empty);
        $this->assertStringNotContainsString('ui-inbox-cards', $empty);
        // DR-51: the settings link is a real header action, not a small floating word.
        $this->assertMatchesRegularExpression('/<div class="ui-page-header__actions"><a href="[^"]*\/dashboard\/requests\/careers\/settings" class="ui-button ui-button--secondary">/', $empty);

        config([
            'careers.identity.encryption_key' => base64_encode(random_bytes(32)),
            'careers.identity.hmac_key' => base64_encode(random_bytes(32)),
            'careers.identity.key_version' => 1,
        ]);
        Storage::fake('careers');
        $result = app(ApplicationValidator::class)->validate($this->validInput(), [$this->cityId()], CarbonImmutable::now('Asia/Amman'));
        $this->assertSame([], $result['errors']);
        $session = UploadSession::query()->create(['expires_at' => now()->addDay()]);
        $cv = app(AttachmentStore::class)->store($session, $this->pdf());
        $this->assertInstanceOf(ApplicationAttachment::class, $cv);
        $consent = app(CareersForm::class)->consent();
        $this->assertNotNull($consent);
        app(ApplicationSubmitter::class)->submit($result['data'], $session, $cv->id, (string) Str::uuid(), $consent);

        $one = $this->html('/dashboard/requests/careers');
        $this->assertStringContainsString('<div class="ui-tiles ui-inbox-cards">', $one);
        $this->assertStringNotContainsString('ui-inbox-summary', $one);
    }

    public function test_a_question_without_ratings_shows_a_quiet_dash_not_words_at_number_size(): void
    {
        // DR-54, the component: a muted dash, the words for screen readers only; a value keeps its usual markup.
        $empty = $this->dom('<x-ui.stat-tile label="مؤشر" :value="null" empty="لا قيمة بعد" />');
        $value = $this->element($empty, '//p['.self::cls('ui-stat-tile__value--empty').']');
        $this->assertSame('true', $this->element($empty, '//p['.self::cls('ui-stat-tile__value').']/span[1]')->getAttribute('aria-hidden'));
        $this->assertSame('—لا قيمة بعد', trim($value->textContent));
        $this->assertSame('لا قيمة بعد', trim($this->element($empty, '//p['.self::cls('ui-stat-tile__value').']/span['.self::cls('ui-visually-hidden').']')->textContent));
        $this->assertSame(0, $this->countMatches($empty, '//bdi'));
        $this->assertSame('—', trim($this->element($this->dom('<x-ui.stat-tile label="مؤشر" :value="null" />'), '//p['.self::cls('ui-stat-tile__value').']')->textContent));
        $this->assertSame('12', $this->element($this->dom('<x-ui.stat-tile label="مؤشر" value="12" />'), '//p[@class="ui-stat-tile__value"]/bdi')->textContent);

        // The feedback board: five questions, none rated yet → five dashes; one rating → that average shows.
        app()->setLocale('ar');
        $none = $this->html('/dashboard/requests/feedback');
        $this->assertSame(5, substr_count($none, 'ui-stat-tile__value--empty'));
        $this->assertStringContainsString('<span class="ui-visually-hidden">لا تقييمات</span>', $none);
        $this->assertStringNotContainsString('<bdi>لا تقييمات</bdi>', $none);

        Feedback::query()->create([
            'branch_id' => (int) Branch::query()->value('id'), 'rating_overall' => 5, 'locale' => 'ar', 'entry_point' => 'direct',
            'idempotency_key' => (string) Str::uuid(), 'form_version' => 'feedback-form-v1', 'submitted_at' => now()->subDay(),
        ]);
        $one = $this->html('/dashboard/requests/feedback');
        $this->assertStringContainsString('<p class="ui-stat-tile__value"><bdi>5.0 من 5</bdi></p>', $one);
        $this->assertSame(4, substr_count($one, 'ui-stat-tile__value--empty'));
    }

    public function test_home_row_settings_links_and_the_menu_search_beside_its_field(): void
    {
        // DR-47: the Command Center tiles share even rows (all side by side on wide screens).
        $this->assertStringContainsString('<div class="ui-tiles ui-tiles--row">', $this->html('/dashboard'));

        // DR-55: the two related-screen links in Settings look the same.
        $settings = $this->html('/dashboard/settings');
        $this->assertMatchesRegularExpression('/<a href="[^"]*\/dashboard\/settings\/consents" class="ui-button ui-button--secondary">/', $settings);
        $this->assertMatchesRegularExpression('/<a href="[^"]*\/dashboard\/content\/texts" class="ui-button ui-button--secondary">/', $settings);
        $actions = $this->capture('/<div class="ui-page-header__actions">(.*?)<\/div>/s', $settings);
        $this->assertStringNotContainsString('ui-button--ghost', $actions);
        $this->assertSame(2, substr_count($actions, 'ui-button--secondary'));

        // DR-49: the search form can put its button beside the field; the button is secondary ("New item" is primary).
        $search = $this->capture('/<form class="ui-inbox-filters ui-menu-search"[^>]*role="search">(.*?)<\/form>/s', $this->html('/dashboard/data/menu'));
        $this->assertMatchesRegularExpression($this->submit('ui-button ui-button--secondary'), $search);
    }
}
