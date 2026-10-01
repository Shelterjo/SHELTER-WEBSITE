<?php

namespace Tests\Feature\DesignSystem;

use Tests\TestCase;

class NavigationAndOverlaysTest extends TestCase
{
    use RendersComponents;

    public function test_breadcrumb_marks_the_current_page_and_mirrors_its_separators(): void
    {
        $xpath = $this->dom('<x-ui.breadcrumb :items="[[\'label\' => \'الرئيسية\', \'href\' => \'/ar/\'], [\'label\' => \'المنيو\']]" />');

        $this->assertSame(__('ui.breadcrumb', [], 'ar'), $this->element($xpath, '//nav')->getAttribute('aria-label'));
        $this->assertSame(1, $this->countMatches($xpath, '//ol/li/a[@href="/ar/"]'));
        $current = $this->element($xpath, '//ol/li[last()]/span');
        $this->assertSame('page', $current->getAttribute('aria-current'));
        $this->assertSame(0, $this->countMatches($xpath, '//ol/li[last()]//a'));
        $this->assertStringContainsString('ui-icon--directional', $this->element($xpath, '//*[local-name()="svg"]')->getAttribute('class'));
    }

    public function test_skip_link_and_nav_link(): void
    {
        $skip = $this->element($this->dom('<x-ui.skip-link />', [], 'en'), '//a');
        $this->assertSame('#main', $skip->getAttribute('href'));
        $this->assertSame('Skip to content', $skip->textContent);

        $current = $this->element($this->dom('<x-ui.nav-link href="/ar/" current icon="house">الرئيسية</x-ui.nav-link>'), '//a');
        $this->assertSame('page', $current->getAttribute('aria-current'));
        $this->assertFalse($this->element($this->dom('<x-ui.nav-link href="/x">x</x-ui.nav-link>'), '//a')->hasAttribute('aria-current'));
    }

    public function test_tabs_render_the_complete_aria_state_on_the_server(): void
    {
        $xpath = $this->dom(<<<'BLADE'
            <x-ui.tabs id="t" label="أقسام" :tabs="['a' => 'الأول', 'b' => 'الثاني']" selected="b">
                <x-ui.tab-panel tab="a">لوحة أ</x-ui.tab-panel>
                <x-ui.tab-panel tab="b">لوحة ب</x-ui.tab-panel>
            </x-ui.tabs>
            BLADE);

        $this->assertSame('أقسام', $this->element($xpath, '//*[@role="tablist"]')->getAttribute('aria-label'));
        $first = $this->element($xpath, '//*[@id="t-tab-a"]');
        $second = $this->element($xpath, '//*[@id="t-tab-b"]');
        $this->assertSame(['tab', 'false', '-1', 't-panel-a'], [$first->getAttribute('role'), $first->getAttribute('aria-selected'), $first->getAttribute('tabindex'), $first->getAttribute('aria-controls')]);
        $this->assertSame(['true', '0'], [$second->getAttribute('aria-selected'), $second->getAttribute('tabindex')]);
        $panelA = $this->element($xpath, '//*[@id="t-panel-a"]');
        $panelB = $this->element($xpath, '//*[@id="t-panel-b"]');
        $this->assertSame(['tabpanel', 't-tab-a'], [$panelA->getAttribute('role'), $panelA->getAttribute('aria-labelledby')]);
        $this->assertTrue($panelA->hasAttribute('hidden'));
        $this->assertFalse($panelB->hasAttribute('hidden'));
        $this->assertTrue($this->element($xpath, '//*[@data-ui-tabs]')->hasAttribute('id'));
    }

    public function test_dialogs_are_native_labelled_and_closable_without_javascript(): void
    {
        foreach (['modal' => 'ui-modal', 'drawer' => 'ui-drawer', 'bottom-sheet' => 'ui-sheet'] as $component => $class) {
            $xpath = $this->dom("<x-ui.{$component} id=\"d\" title=\"تفاصيل\">محتوى</x-ui.{$component}>");
            $dialog = $this->element($xpath, '//dialog');
            $this->assertStringContainsString($class, $dialog->getAttribute('class'), $component);
            $this->assertSame('d-title', $dialog->getAttribute('aria-labelledby'));
            $this->assertSame('any', $dialog->getAttribute('closedby'));
            $title = $this->element($xpath, '//*[@id="d-title"]');
            $this->assertSame(['-1', true], [$title->getAttribute('tabindex'), $title->hasAttribute('autofocus')], 'Focus starts on the title.');
            $close = $this->element($xpath, '//form[@method="dialog"]/button');
            $this->assertSame('submit', $close->getAttribute('type'));
            $this->assertSame(__('ui.close', [], 'ar'), $close->getAttribute('aria-label'));
        }

        $sheet = $this->dom('<x-ui.bottom-sheet id="s" title="x">y</x-ui.bottom-sheet>');
        $this->assertSame('true', $this->element($sheet, '//*['.self::cls('ui-sheet__handle').']')->getAttribute('aria-hidden'));
        $start = $this->element($this->dom('<x-ui.drawer id="n" title="x" side="start">y</x-ui.drawer>'), '//dialog');
        $this->assertStringContainsString('ui-drawer--start', $start->getAttribute('class'));
        $this->assertSame('Close', $this->element($this->dom('<x-ui.modal id="m" title="x">y</x-ui.modal>', [], 'en'), '//form/button')->getAttribute('aria-label'));
        $this->assertRenderFails('<x-ui.dialog id="x" title="x" variant="popup">y</x-ui.dialog>', 'unknown variant');
    }

    public function test_disclosure_is_a_native_details_element(): void
    {
        $xpath = $this->dom('<x-ui.disclosure summary="سؤال" open>جواب</x-ui.disclosure>');

        $this->assertTrue($this->element($xpath, '//details')->hasAttribute('open'));
        $this->assertSame('سؤال', trim($this->element($xpath, '//details/summary')->textContent));
        $this->assertFalse($this->element($this->dom('<x-ui.disclosure summary="سؤال">جواب</x-ui.disclosure>'), '//details')->hasAttribute('open'));
    }
}
