<?php

namespace Tests\Feature\DesignSystem;

use Tests\TestCase;

class ButtonAndIconTest extends TestCase
{
    use RendersComponents;

    public function test_variant_and_size_come_from_one_component(): void
    {
        $button = $this->element($this->dom('<x-ui.button variant="secondary" size="lg">حفظ</x-ui.button>'), '//button');

        $this->assertSame('ui-button ui-button--secondary ui-button--lg', $button->getAttribute('class'));
        $this->assertSame('button', $button->getAttribute('type'));
        $this->assertSame('حفظ', trim($button->textContent));
        $this->assertRenderFails('<x-ui.button variant="fancy">x</x-ui.button>', 'unknown variant');
        $this->assertRenderFails('<x-ui.button size="xl">x</x-ui.button>', 'unknown size');
    }

    public function test_href_renders_a_link_and_a_disabled_link_loses_its_href(): void
    {
        $link = $this->element($this->dom('<x-ui.button href="/ar/" variant="link">x</x-ui.button>'), '//a');
        $this->assertSame('/ar/', $link->getAttribute('href'));
        $this->assertFalse($link->hasAttribute('type'));

        $disabled = $this->element($this->dom('<x-ui.button href="/ar/" disabled>x</x-ui.button>'), '//a');
        $this->assertFalse($disabled->hasAttribute('href'));
        $this->assertSame('link', $disabled->getAttribute('role'));
        $this->assertSame('true', $disabled->getAttribute('aria-disabled'));

        $button = $this->element($this->dom('<x-ui.button disabled>x</x-ui.button>'), '//button');
        $this->assertTrue($button->hasAttribute('disabled'));
    }

    public function test_loading_keeps_the_label_and_focus_and_announces_busy(): void
    {
        $xpath = $this->dom('<x-ui.button loading>حفظ</x-ui.button>');
        $button = $this->element($xpath, '//button');

        $this->assertSame('true', $button->getAttribute('aria-busy'));
        $this->assertSame('true', $button->getAttribute('aria-disabled'));
        $this->assertFalse($button->hasAttribute('disabled'), 'A loading button keeps focus (no native disabled).');
        $this->assertStringContainsString('حفظ', $button->textContent);
        $this->assertStringContainsString(__('ui.loading', [], 'ar'), $button->textContent);
        $this->assertSame('true', $this->element($xpath, '//button/*[local-name()="svg"]')->getAttribute('aria-hidden'));
    }

    public function test_icon_only_buttons_must_have_an_accessible_name(): void
    {
        $this->assertRenderFails('<x-ui.button icon="x" icon-only />', 'accessible name');

        $button = $this->element($this->dom('<x-ui.button icon="x" icon-only label="إغلاق">ignored</x-ui.button>'), '//button');
        $this->assertSame('إغلاق', $button->getAttribute('aria-label'));
        $this->assertStringContainsString('ui-button--icon-only', $button->getAttribute('class'));
        $this->assertSame('', trim($button->textContent));
    }

    public function test_a_button_can_open_a_dialog_through_invoker_commands(): void
    {
        $button = $this->element($this->dom('<x-ui.button opens="detail">x</x-ui.button>'), '//button');

        $this->assertSame('detail', $button->getAttribute('commandfor'));
        $this->assertSame('show-modal', $button->getAttribute('command'));
        $this->assertSame('dialog', $button->getAttribute('aria-haspopup'));
        $this->assertSame('detail', $button->getAttribute('data-ui-dialog-open'));
    }

    public function test_icons_are_decorative_unless_labelled(): void
    {
        $svg = $this->element($this->dom('<x-ui.icon name="x" />'), '//*[local-name()="svg"]');
        $this->assertSame('true', $svg->getAttribute('aria-hidden'));
        $this->assertSame('false', $svg->getAttribute('focusable'));
        $this->assertFalse($svg->hasAttribute('role'));

        $labelled = $this->element($this->dom('<x-ui.icon name="info" :label="$label" />', ['label' => 'معلومة "مهمة" <b>']), '//*[local-name()="svg"]');
        $this->assertSame('img', $labelled->getAttribute('role'));
        $this->assertSame('معلومة "مهمة" <b>', $labelled->getAttribute('aria-label'), 'The label is escaped, never markup.');
        $this->assertFalse($labelled->hasAttribute('aria-hidden'));
    }

    public function test_icon_sizes_and_rtl_mirroring(): void
    {
        $class = fn (string $tag): string => $this->element($this->dom($tag), '//*[local-name()="svg"]')->getAttribute('class');

        $this->assertSame('ui-icon ui-icon--sm', $class('<x-ui.icon name="x" size="sm" />'));
        $this->assertSame('ui-icon ui-icon--lg', $class('<x-ui.icon name="x" size="lg" />'));
        $this->assertStringContainsString('ui-icon--directional', $class('<x-ui.icon name="chevron-right" />'));
        $this->assertStringContainsString('ui-icon--directional', $class('<x-ui.icon name="log-out" />'));
        $this->assertStringNotContainsString('ui-icon--directional', $class('<x-ui.icon name="x" />'), 'Close never mirrors.');
        $this->assertRenderFails('<x-ui.icon name="x" size="xl" />', 'unknown size');
    }

    public function test_only_allow_listed_lucide_icons_with_the_token_stroke(): void
    {
        $this->assertRenderFails('<x-ui.icon name="rocket" />', 'unknown icon');
        $this->assertRenderFails('<x-ui.icon name="../views/welcome" />', 'unknown icon');

        $tokens = json_decode((string) file_get_contents(base_path('design-system/tokens/tokens.json')), true);
        $this->assertIsArray($tokens);
        $stroke = (string) data_get($tokens, 'size.icon.stroke.$value');
        $files = glob(resource_path('icons/*.svg')) ?: [];
        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $svg = (string) file_get_contents($file);
            preg_match_all('/stroke-width="([^"]+)"/', $svg, $widths);
            $this->assertSame([$stroke], array_values(array_unique($widths[1])), basename($file).' must use the token stroke');
            $this->assertStringNotContainsString('<!--', $svg);
        }
    }
}
