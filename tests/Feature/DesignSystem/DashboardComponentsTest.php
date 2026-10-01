<?php

namespace Tests\Feature\DesignSystem;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardComponentsTest extends TestCase
{
    use RendersComponents;

    /** @return array<string, array{string, string}> */
    public static function statuses(): array
    {
        return [
            'SYNCED' => ['SYNCED', 'circle-check'],
            'PENDING' => ['PENDING', 'clock'],
            'FAILED' => ['FAILED', 'circle-x'],
            'NOT SUPPORTED' => ['NOT SUPPORTED', 'ban'],
            'MANUAL ACTION REQUIRED' => ['MANUAL ACTION REQUIRED', 'hand'],
            'OUT OF SYNC' => ['OUT OF SYNC', 'refresh-cw-off'],
        ];
    }

    #[DataProvider('statuses')]
    public function test_every_sync_status_has_an_icon_and_text(string $status, string $icon): void
    {
        $key = strtolower(str_replace(' ', '_', $status));
        $expectedSvg = (string) file_get_contents(resource_path("icons/{$icon}.svg"));
        preg_match('/<path d="([^"]+)"/', $expectedSvg, $path);
        foreach (['ar', 'en'] as $locale) {
            $xpath = $this->dom('<x-ui.status-pill :status="$s" />', ['s' => $status], $locale);
            $pill = $this->element($xpath, '//span['.self::cls('ui-status-pill').']');
            $this->assertSame($status, $pill->getAttribute('data-status'));
            $this->assertSame(__("ui.status.{$key}", [], $locale), trim($this->element($xpath, '//span/span')->textContent));
            $this->assertSame('true', $this->element($xpath, '//*[local-name()="svg"]')->getAttribute('aria-hidden'));
            $this->assertSame(1, $this->countMatches($xpath, '//*[local-name()="path" and @d="'.($path[1] ?? '').'"]'), "{$status} uses {$icon}");
        }
    }

    public function test_status_pill_accepts_keys_and_rejects_unknown_statuses(): void
    {
        $this->assertSame('OUT OF SYNC', $this->element($this->dom('<x-ui.status-pill status="out_of_sync" />'), '//span')->getAttribute('data-status'));
        $this->assertRenderFails('<x-ui.status-pill status="DONE" />', 'unknown status');
    }

    public function test_stat_tile_shows_the_trend_with_icon_and_words(): void
    {
        $xpath = $this->dom('<x-ui.stat-tile label="مؤشر" value="12" trend="up" trend-text="+3" hint="ملاحظة" href="/dashboard/" />');

        $this->assertStringContainsString('ui-card', $this->element($xpath, '//div')->getAttribute('class'));
        $this->assertSame('12', $this->element($xpath, '//bdi')->textContent);
        $trend = $this->element($xpath, '//p['.self::cls('ui-stat-tile__trend').']');
        $this->assertStringContainsString(__('ui.trend.up', [], 'ar'), $trend->textContent);
        $this->assertSame(1, $this->countMatches($xpath, '//p['.self::cls('ui-stat-tile__trend').']/*[local-name()="svg"]'));
        $this->assertSame('/dashboard/', $this->element($xpath, '//a['.self::cls('ui-stretched').']')->getAttribute('href'));
        $this->assertRenderFails('<x-ui.stat-tile label="x" value="1" trend="sideways" trend-text="x" />', 'unknown trend');
    }

    public function test_sidebar_page_header_and_toolbar(): void
    {
        $sidebar = $this->dom('<ul class="ui-sidebar"><x-ui.sidebar-item href="/dashboard/" icon="house" current :count="3">مركز التحكم</x-ui.sidebar-item></ul>');
        $link = $this->element($sidebar, '//li/a');
        $this->assertSame('page', $link->getAttribute('aria-current'));
        $this->assertStringContainsString('ui-nav-link', $link->getAttribute('class'));
        $this->assertSame('3', $this->element($sidebar, '//bdi')->textContent);

        $header = $this->dom('<x-ui.page-header title="صفحة" description="وصف"><x-slot:actions><x-ui.button>إضافة</x-ui.button></x-slot:actions></x-ui.page-header>');
        $this->assertSame(1, $this->countMatches($header, '//h1'));
        $this->assertSame(1, $this->countMatches($header, '//*['.self::cls('ui-page-header__actions').']/button'));

        $toolbar = $this->element($this->dom('<x-ui.toolbar label="أدوات"><x-ui.button>تصفية</x-ui.button></x-ui.toolbar>'), '//div');
        $this->assertSame(['group', 'أدوات'], [$toolbar->getAttribute('role'), $toolbar->getAttribute('aria-label')]);
    }
}
