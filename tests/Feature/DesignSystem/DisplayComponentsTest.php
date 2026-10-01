<?php

namespace Tests\Feature\DesignSystem;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DisplayComponentsTest extends TestCase
{
    use RendersComponents;

    public function test_product_card_without_an_approved_image_has_no_image_box(): void
    {
        $xpath = $this->dom('<x-ui.product-card name="قهوة تركية سينجل" secondary="TURKISH COFFEE SINGLE" secondary-lang="en" :price="1500" />');

        $this->assertSame(0, $this->countMatches($xpath, '//*['.self::cls('ui-card__media').']'));
        $this->assertStringNotContainsString('ui-product-card--has-media', $this->element($xpath, '//article')->getAttribute('class'));
        $this->assertSame('en', $this->element($xpath, '//*['.self::cls('ui-product-card__secondary').']')->getAttribute('lang'));
    }

    public function test_product_card_images_need_explicit_dimensions(): void
    {
        $this->assertRenderFails(
            '<x-ui.product-card name="x" :price="1000"><x-slot:media><img src="/a.avif" alt=""></x-slot:media></x-ui.product-card>',
            'explicit width and height',
        );

        $xpath = $this->dom('<x-ui.product-card name="x" :price="1000"><x-slot:media><img src="/a.avif" alt="" width="240" height="240"></x-slot:media></x-ui.product-card>');
        $this->assertSame(1, $this->countMatches($xpath, '//*['.self::cls('ui-card__media').']/img'));
        $this->assertStringContainsString('ui-product-card--has-media', $this->element($xpath, '//article')->getAttribute('class'));
    }

    public function test_the_whole_card_is_one_button_described_by_its_details(): void
    {
        $xpath = $this->dom('<x-ui.product-card id="p1" name="CAPPUCCINO" name-lang="en" :price="2500" badge="جديد" status="غير متوفر حاليًا" unavailable opens="detail" />');

        $this->assertSame(1, $this->countMatches($xpath, '//button'));
        $button = $this->element($xpath, '//h3/button');
        $this->assertSame('p1-badge p1-status p1-price', $button->getAttribute('aria-describedby'));
        foreach (explode(' ', $button->getAttribute('aria-describedby')) as $id) {
            $this->assertSame(1, $this->countMatches($xpath, "//*[@id='{$id}']"), "Missing description target {$id}");
        }
        $this->assertSame('detail', $button->getAttribute('commandfor'));
        $this->assertSame('en', $this->element($xpath, '//h3/button/span')->getAttribute('lang'));
        $this->assertStringContainsString('ui-product-card--unavailable', $this->element($xpath, '//article')->getAttribute('class'));
        $this->assertSame(1, $this->countMatches($xpath, '//*['.self::cls('ui-badge').']'));

        $link = $this->element($this->dom('<x-ui.product-card name="x" href="/ar/jo/menu/#p-x" :price="1000" />'), '//h3/a');
        $this->assertSame('/ar/jo/menu/#p-x', $link->getAttribute('href'));
        $this->assertSame(0, $this->countMatches($this->dom('<x-ui.product-card name="x" />'), '//button | //a'));
    }

    public function test_price_is_two_decimals_isolated_and_spoken_in_full(): void
    {
        $ar = $this->dom('<x-ui.price :fils="2500" />');
        $this->assertSame('2.50', $this->element($ar, '//bdi')->textContent);
        $this->assertSame('true', $this->element($ar, '//bdi/..')->getAttribute('aria-hidden'));
        $this->assertSame('2.50 دينار أردني', $this->element($ar, '//*['.self::cls('ui-visually-hidden').']')->textContent);
        $this->assertStringContainsString('د.أ', $this->element($ar, '//bdi/..')->textContent);

        $en = $this->dom('<x-ui.price :fils="3250" />', [], 'en');
        $this->assertSame('3.25 Jordanian dinars', $this->element($en, '//*['.self::cls('ui-visually-hidden').']')->textContent);
        $this->assertStringContainsString('JOD', $this->element($en, '//bdi/..')->textContent);
        $this->assertRenderFails('<x-ui.price :fils="100" currency="USD" />', 'unsupported currency');
    }

    public function test_card_and_event_card_share_the_card_base(): void
    {
        $card = $this->dom('<x-ui.card title="عنوان" href="/x"><p>نص</p><x-slot:footer><x-ui.button>إجراء</x-ui.button></x-slot:footer></x-ui.card>');
        $this->assertSame('/x', $this->element($card, '//h3/a['.self::cls('ui-stretched').']')->getAttribute('href'));
        $this->assertSame(0, $this->countMatches($card, '//*['.self::cls('ui-card__media').']'));
        $this->assertSame(1, $this->countMatches($card, '//*['.self::cls('ui-card__footer').']/button'));

        $event = $this->dom('<x-ui.event-card title="فعالية تجريبية" date="تاريخ تجريبي" datetime="2026-01-01" place="مكان تجريبي" />');
        $this->assertStringContainsString('ui-card', $this->element($event, '//article')->getAttribute('class'));
        $this->assertSame('2026-01-01', $this->element($event, '//time')->getAttribute('datetime'));
        $this->assertSame(2, $this->countMatches($event, '//ul[@role="list"]/li/*[local-name()="svg" and @aria-hidden="true"]'));
        $this->assertStringContainsString(__('ui.event.place', [], 'ar'), $this->element($event, '//ul/li[2]')->textContent);
    }

    public function test_alert_badge_and_chip_never_rely_on_colour_alone(): void
    {
        $alert = $this->dom('<x-ui.alert variant="danger" title="تعذّر الحفظ">نص</x-ui.alert>');
        $root = $this->element($alert, '//div['.self::cls('ui-alert').']');
        $this->assertSame('alert', $root->getAttribute('role'));
        $this->assertSame(1, $this->countMatches($alert, '//div['.self::cls('ui-alert').']/*[local-name()="svg"]'));
        $this->assertStringContainsString(__('ui.alert.danger', [], 'ar'), $root->textContent);
        $this->assertSame('status', $this->element($this->dom('<x-ui.alert>x</x-ui.alert>'), '//div')->getAttribute('role'));

        $pressed = $this->element($this->dom('<x-ui.chip pressed>ساخن</x-ui.chip>'), '//button');
        $this->assertSame('true', $pressed->getAttribute('aria-pressed'));
        $this->assertSame(1, $this->countMatches($this->dom('<x-ui.chip pressed>ساخن</x-ui.chip>'), '//button/*[local-name()="svg"]'));
        $this->assertSame('false', $this->element($this->dom('<x-ui.chip>ساخن</x-ui.chip>'), '//button')->getAttribute('aria-pressed'));
        $this->assertSame('true', $this->element($this->dom('<x-ui.chip href="#" current>بارد</x-ui.chip>'), '//a')->getAttribute('aria-current'));
        $this->assertRenderFails('<x-ui.badge variant="purple">x</x-ui.badge>', 'unknown variant');
    }

    public function test_loading_and_empty_states(): void
    {
        $skeleton = $this->element($this->dom('<x-ui.skeleton :lines="2" media />'), '//div');
        $this->assertSame('status', $skeleton->getAttribute('role'));
        $this->assertStringContainsString(__('ui.loading', [], 'ar'), $skeleton->textContent);
        $this->assertSame('true', $this->element($this->dom('<x-ui.skeleton decorative />'), '//div')->getAttribute('aria-hidden'));

        $empty = $this->dom('<x-ui.empty-state title="لا توجد نتائج">جرّب كلمة أخرى<x-slot:actions><x-ui.button>مسح</x-ui.button></x-slot:actions></x-ui.empty-state>');
        $this->assertSame('لا توجد نتائج', trim($this->element($empty, '//h2')->textContent));
        $this->assertSame(1, $this->countMatches($empty, '//button'));
        $this->assertStringContainsString(__('ui.media_pending', [], 'ar'), $this->render('<x-ui.media-placeholder />'));
    }

    public function test_table_has_a_caption_scoped_headers_and_a_focusable_scroll_region(): void
    {
        $xpath = $this->dom('<x-ui.table id="t" caption="جدول" :columns="$columns" :rows="$rows" row-header="name" />', [
            'columns' => [['key' => 'name', 'label' => 'الاسم'], ['key' => 'count', 'label' => 'العدد', 'numeric' => true]],
            'rows' => [['name' => 'أ', 'count' => 3]],
        ]);

        $region = $this->element($xpath, '//div[@role="region"]');
        $this->assertSame('t-caption', $region->getAttribute('aria-labelledby'));
        $this->assertSame('0', $region->getAttribute('tabindex'));
        $this->assertSame('جدول', $this->element($xpath, '//caption[@id="t-caption"]')->textContent);
        $this->assertSame(2, $this->countMatches($xpath, '//thead/tr/th[@scope="col"]'));
        $this->assertSame('أ', $this->element($xpath, '//tbody/tr/th[@scope="row"]')->textContent);
        $this->assertSame('العدد', $this->element($xpath, '//tbody/tr/td')->getAttribute('data-label'));
        $this->assertStringContainsString('ui-table__numeric', $this->element($xpath, '//tbody/tr/td')->getAttribute('class'));

        $stacked = $this->dom('<x-ui.table caption="جدول" stack :columns="[[\'key\' => \'a\', \'label\' => \'أ\']]" :rows="[[\'a\' => 1]]" />');
        $this->assertSame('table', $this->element($stacked, '//table')->getAttribute('role'));
        $this->assertSame('cell', $this->element($stacked, '//td')->getAttribute('role'));
    }

    /** @return array<string, array{int, int, list<string>}> */
    public static function paginationWindows(): array
    {
        return [
            'middle' => [5, 9, ['1', '…', '4', '5', '6', '…', '9']],
            'first' => [1, 9, ['1', '2', '…', '9']],
            'one-page gap shows the page' => [3, 9, ['1', '2', '3', '4', '…', '9']],
            'last' => [9, 9, ['1', '…', '8', '9']],
            'few pages' => [2, 3, ['1', '2', '3']],
        ];
    }

    /** @param list<string> $expected */
    #[DataProvider('paginationWindows')]
    public function test_pagination_window(int $current, int $total, array $expected): void
    {
        $xpath = $this->dom('<x-ui.pagination :current="$c" :total="$t" url="?page={page}" />', ['c' => $current, 't' => $total]);

        $pages = [];
        foreach ($xpath->query('//li['.self::cls('ui-pagination__page').']') ?: [] as $item) {
            $pages[] = trim((string) $item->nodeValue);
        }
        $this->assertSame($expected, $pages);
        $currentLink = $this->element($xpath, '//a[@aria-current="page"]');
        $this->assertSame((string) $current, $currentLink->textContent);
        $this->assertSame("?page={$current}", $currentLink->getAttribute('href'));
        $this->assertSame(__('ui.pagination.page_of', ['page' => $current, 'total' => $total], 'ar'), trim($this->element($xpath, '//li['.self::cls('ui-pagination__summary').']')->textContent));
        $this->assertSame(__('ui.pagination.label', [], 'ar'), $this->element($xpath, '//nav')->getAttribute('aria-label'));
    }

    public function test_pagination_edges_and_single_page(): void
    {
        $first = $this->dom('<x-ui.pagination :current="1" :total="4" :url="fn (int $page) => \'/p/\'.$page.\'/\'" />');
        $previous = $this->element($first, '//ul/li[1]/a');
        $this->assertFalse($previous->hasAttribute('href'));
        $this->assertSame('true', $previous->getAttribute('aria-disabled'));
        $this->assertSame('/p/2/', $this->element($first, '//a[@rel="next"]')->getAttribute('href'));
        $this->assertSame('', trim($this->render('<x-ui.pagination :current="1" :total="1" url="?p={page}" />')));
    }
}
