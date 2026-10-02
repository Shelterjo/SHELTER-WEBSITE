<?php

namespace Tests\Feature\Site;

use App\Models\Branch;
use App\Models\Market;
use App\Support\Bidi;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The final copy audit (2026-10-02) and the UX-006 design review, package B1: functional wording in both languages,
 * mixed-direction text handled at render time (DR-07, DR-12, DR-17), the same branch actions everywhere (DR-08,
 * DR-09, DR-10), the gateway's market and llms.txt (PUBLIC-ROUTE-MAP RM-06, RM-10). Approved wording is untouched.
 */
class CopyAndBidiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function page(string $path): string
    {
        return (string) $this->get($path)->assertOk()->getContent();
    }

    public function test_a_name_in_the_other_language_keeps_its_own_direction_on_the_menu(): void
    {
        // DR-07: the Arabic secondary name on the English menu keeps its brackets in place.
        $en = $this->page('/en/jo/menu/');
        $this->assertMatchesRegularExpression('#<p class="ui-product-card__secondary" id="[^"]+" lang="ar" dir="rtl">آيس كراميل لاتيه \(بدون سكر\)</p>#u', $en);
        // … and an English name on the Arabic menu (pending Arabic names, P-01) does the same, chips and the sheet too.
        $ar = $this->page('/ar/jo/menu/');
        $this->assertMatchesRegularExpression('#<span lang="en" dir="ltr">[A-Z][^<]*</span></button>#', $ar);
        $this->assertSame(1, preg_match('#<a\b[^>]*data-ui-menu-nav="spring"[^>]*>#', $ar, $chip));
        $this->assertStringContainsString('lang="en"', $chip[0] ?? '');
        $this->assertStringContainsString('dir="ltr"', $chip[0] ?? '');
        $this->assertMatchesRegularExpression('#class="ui-menu__all-link"[^>]*lang="en" dir="ltr"#', $ar);
    }

    public function test_menu_prices_line_and_badges(): void
    {
        $this->assertStringContainsString('<p class="ui-page-intro__lead">Prices in Jordanian dinars (JOD), tax included</p>', $this->page('/en/jo/menu/'));
        $this->assertStringContainsString('<p class="ui-page-intro__lead">الأسعار بالدينار الأردني، وتشمل الضريبة.</p>', $this->page('/ar/jo/menu/'));
        $this->assertStringNotContainsString('VAT', (string) __('menu.prices_note', [], 'en'), 'Jordan levies a sales tax, not VAT (copy audit F01)');
        $this->assertSame('Seasonal', __('menu.seasonal', [], 'en'), 'capitals come from the badge style (F56)');
        $this->assertSame('لا أصناف', trans_choice('menu.items_count', 0, ['count' => 0], 'ar'), 'F37');
    }

    public function test_branch_cards_carry_the_same_labelled_actions_with_directions(): void
    {
        foreach (['/ar/' => ['الاتجاهات', 'اتصال', 'راسلنا على واتساب', 'شلتر كوفي درايف'], '/ar/jo/locations/' => ['الاتجاهات', 'اتصال', 'راسلنا على واتساب', 'شلتر كوفي هاوس'],
            '/en/' => ['Directions', 'Call', 'Message us on WhatsApp', 'SHELTER COFFEE DRIVE']] as $path => [$directions, $call, $whatsapp, $name]) {
            $html = $this->page($path);
            preg_match_all('#<div class="ui-branch__actions">(.*?)</div>#s', $html, $groups);
            $this->assertCount(2, $groups[1], $path.': one group per branch');
            foreach ($groups[1] as $group) {
                // DR-08: labelled, in the D-061 order; DR-10: Directions from the approved Maps link; F42: the branch named.
                $this->assertStringNotContainsString('ui-button--icon-only', $group);
                $this->assertMatchesRegularExpression('#href="https://[^"]+"[^>]*>.*?'.preg_quote($directions, '#').'.*?href="tel:\+962799009436".*?'.preg_quote($call, '#').'.*?href="https://wa\.me/962799009436".*?'.preg_quote($whatsapp, '#').'#su', $group);
                $this->assertSame(3, substr_count($group, '<span class="ui-visually-hidden"> — '));
            }
            $this->assertStringContainsString('<span class="ui-visually-hidden"> — '.$name.'</span>', $html);
        }
        $this->assertStringContainsString('href="https://maps.app.goo.gl/zNfDbkxcT1aMdQiWA"', $this->page('/ar/'), 'the same approved field as the branch page (D-336)');
        $this->assertStringContainsString('<p class="ui-branch__more" aria-hidden="true"><span>الساعات والتفاصيل</span>', $this->page('/ar/'), 'the arrow became words');

        // No Maps link saved → no Directions on that card (nothing is made up).
        Branch::query()->where('slug', 'house')->update(['maps_url' => null]);
        $html = $this->page('/en/jo/locations/');
        $this->assertSame(3, substr_count($html, '<span class="ui-visually-hidden"> — SHELTER COFFEE DRIVE</span>'), 'DRIVE keeps all three');
        $this->assertSame(2, substr_count($html, '<span class="ui-visually-hidden"> — SHELTER COFFEE HOUSE</span>'), 'HOUSE: call and WhatsApp only');
        $this->assertMatchesRegularExpression('#Directions<span class="ui-visually-hidden"> — SHELTER COFFEE DRIVE</span>#', $html);
        $this->assertDoesNotMatchRegularExpression('#Directions<span class="ui-visually-hidden"> — SHELTER COFFEE HOUSE</span>#', $html);
    }

    public function test_the_branch_page_groups_its_actions_next_to_the_live_state(): void
    {
        // DR-09: in the header (shown from 1024px; phones keep the sticky bar), Directions · Call · WhatsApp.
        $html = $this->page('/en/jo/locations/irbid/drive/');
        $header = substr($html, (int) strpos($html, '<header class="ui-page-intro">'));
        $header = substr($header, 0, (int) strpos($header, '</header>'));
        $this->assertStringContainsString('class="ui-cluster ui-branch-page__actions" role="group" aria-label="Quick actions"', $header);
        $directions = strpos($header, 'href="https://maps.app.goo.gl/');
        $call = strpos($header, 'href="tel:+962799009436"');
        $whatsapp = strpos($header, 'href="https://wa.me/962799009436"');
        $this->assertNotFalse($directions);
        $this->assertTrue($directions < $call && $call < $whatsapp, 'D-061 order');
        $this->assertStringContainsString('Message us on WhatsApp', $header, 'there is room for the full D-063 label');
        $this->assertStringContainsString('class="ui-action-bar"', $html, 'the phone bar is still there');
    }

    public function test_the_location_section_shows_only_what_exists(): void
    {
        // F03: «مكان الفرع» / “Location” — the address is MISSING (PO-010), services and payments are not approved.
        $ar = $this->page('/ar/jo/locations/irbid/drive/');
        $this->assertStringContainsString('<h2 class="ui-split__title" id="branch-place">مكان الفرع</h2>', $ar);
        $this->assertStringContainsString('بجانب منطقة قصر النخيل / أرابيلا', $ar);
        $en = $this->page('/en/jo/locations/irbid/drive/');
        $this->assertStringContainsString('<h2 class="ui-split__title" id="branch-place">Location</h2>', $en);
        foreach (['Address and services', 'العنوان والخدمات', '>Services<', '>Payment methods<'] as $absent) {
            $this->assertStringNotContainsString($absent, $en.$ar);
        }
    }

    public function test_phone_numbers_have_one_form_per_language_and_stay_left_to_right(): void
    {
        // DR-12: one formatter per language (D-065: 0799009436 · +962 79 900 9436), always an isolated LTR run.
        $ar = $this->page('/ar/jo/locations/irbid/drive/');
        $this->assertStringContainsString('<a href="tel:+962799009436"><bdi dir="ltr">0799009436</bdi></a>', $ar);
        $this->assertStringContainsString('<bdi dir="ltr">0799009436</bdi>', substr($ar, (int) strpos($ar, '<footer')));
        $en = $this->page('/en/contact/');
        $this->assertStringContainsString('<bdi dir="ltr">+962 79 900 9436</bdi>', $en);
        $this->assertStringNotContainsString('<span dir="ltr">+962', $en);
    }

    public function test_latin_names_in_arabic_text_are_isolated_without_changing_the_text(): void
    {
        // DR-17 (the franchise page uses it for the Owner's text).
        $html = (string) Bidi::isolate('تطورت عبر SHELTER COFFEE DRIVE وSHELTER COFFEE HOUSE، مع V60. منذ 2019.', 'ar');
        $this->assertSame('تطورت عبر <bdi dir="ltr" lang="en" class="ui-nowrap">SHELTER COFFEE DRIVE</bdi> و<bdi dir="ltr" lang="en" class="ui-nowrap">SHELTER COFFEE HOUSE</bdi>، مع <bdi dir="ltr" lang="en" class="ui-nowrap">V60</bdi>. منذ 2019.', $html,
            'the full stop and the Arabic comma stay outside; a number alone is left as it is');
        $this->assertSame('قبل <bdi dir="ltr" lang="en">one two three four words</bdi> بعد', (string) Bidi::isolate('قبل one two three four words بعد', 'ar'), 'a long run may still wrap');
        $this->assertSame('Grow With SHELTER COFFEE', (string) Bidi::isolate('Grow With SHELTER COFFEE', 'ar'), 'no Arabic, nothing to isolate');
        $this->assertSame('SHELTER &lt;b&gt; شلتر', (string) Bidi::isolate('SHELTER <b> شلتر', 'en'), 'escaped, untouched in English');
        $escaped = (string) Bidi::isolate('A&B <script>x</script> شلتر', 'ar');
        $this->assertStringNotContainsString('<script>', $escaped, 'the text is escaped before it is split');
        $this->assertStringContainsString('<bdi dir="ltr" lang="en" class="ui-nowrap">A&amp;B</bdi> &lt;', $escaped);
        $this->assertSame('rtl', Bidi::dir('ar'));
        $this->assertSame('ltr', Bidi::dir('en'));
        $this->assertNull(Bidi::dir(null));
    }

    public function test_the_generic_client_error_page_shows_the_real_status(): void
    {
        // F02: GET on an upload address is a 405; the page says 405, never the placeholder "4xx".
        $html = (string) $this->get('/ar/careers/uploads/')->assertStatus(405)->getContent();
        $this->assertStringContainsString('<p class="ui-error__code" aria-hidden="true">405</p>', $html);
        $this->assertStringNotContainsString('4xx', $html);
        // F27: Arabic tab titles end with the Arabic brand, as the approved Google titles do.
        $this->assertStringContainsString('<title>تعذّر فتح هذه الصفحة — شلتر كوفي</title>', $html);
    }

    public function test_tab_titles_use_the_brand_in_the_page_language(): void
    {
        $this->assertStringContainsString('<title>البحث — شلتر كوفي</title>', $this->page('/ar/search/'));
        $this->assertStringContainsString('<title>Search — SHELTER COFFEE</title>', $this->page('/en/search/'));
        $this->assertStringContainsString('<title>الصفحة غير موجودة — شلتر كوفي</title>', (string) $this->get('/ar/no-such-page/')->assertNotFound()->getContent());
        $this->assertStringContainsString('<title>متابعة طلب التوظيف — شلتر كوفي</title>', $this->page('/ar/careers/track/'));
    }

    public function test_functional_wording_follows_the_copy_audit(): void
    {
        $home = $this->page('/ar/');
        $this->assertStringContainsString('الفروع وساعات الدوام', $home, 'F06: one term for opening hours');
        $this->assertStringContainsString('تُحدَّث الحالة تلقائيًا وفق ساعات الدوام.', $home, 'F05');
        $this->assertStringContainsString('روابط سريعة', $home, 'F16');
        $this->assertStringContainsString('Browse the menu', $this->page('/en/'), 'F20');
        $contact = $this->page('/en/contact/');
        $this->assertStringContainsString('Franchise Inquiries', $contact, 'F08: the D-071 label, one case style');
        $this->assertStringContainsString('All times are Jordan time.', $this->page('/en/jo/locations/'), 'F18');
        $this->assertStringContainsString('Apply through our application form (in Arabic) and attach your CV.', $this->page('/en/careers/'), 'F07');
        // F09 / F43: Arabic number agreement in the dashboard counts.
        $this->assertSame('طلبان', trans_choice('dashboard.requests.results', 2, [], 'ar'));
        $this->assertSame('5 طلبات', trans_choice('dashboard.requests.results', 5, [], 'ar'));
        $this->assertSame('11 طلبًا', trans_choice('dashboard.requests.results', 11, [], 'ar'));
        $this->assertSame('اسمان عربيان بانتظار مراجعتك', trans_choice('dashboard.menu.pending_names', 2, [], 'ar'));
        $this->assertSame('نزّل مرفقات 12 طلبًا', trans_choice('dashboard.requests.export.zip_button', 12, [], 'ar'));
    }

    public function test_the_gateway_links_to_the_active_market_not_a_written_code(): void
    {
        // RM-06: rename the market's code and the gateway follows; no active market → no shortcuts at all.
        Market::query()->where('code', 'jo')->update(['code' => 'qa']);
        $this->app->forgetScopedInstances(); // each real request starts with a fresh market lookup
        $html = $this->page('/');
        $this->assertStringContainsString('href="http://localhost/ar/qa/menu/"', $html);
        $this->assertStringContainsString('href="http://localhost/en/qa/locations/"', $html);
        $this->assertStringNotContainsString('/jo/', $html);

        Market::query()->update(['is_active' => false]);
        $this->app->forgetScopedInstances();
        $this->assertStringNotContainsString('ui-gateway__shortcuts', $this->page('/'));
    }

    public function test_llms_txt_lists_careers_like_the_sitemap(): void
    {
        // RM-10
        $text = (string) $this->get('/llms.txt')->assertOk()->getContent();
        $this->assertStringContainsString('- [Careers](http://localhost/en/careers/)', $text);
        $this->assertStringContainsString('- [التوظيف](http://localhost/ar/careers/)', $text);
    }
}
