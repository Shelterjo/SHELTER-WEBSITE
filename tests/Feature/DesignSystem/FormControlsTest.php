<?php

namespace Tests\Feature\DesignSystem;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class FormControlsTest extends TestCase
{
    use RendersComponents;

    public function test_field_links_label_hint_and_error_to_the_control(): void
    {
        $xpath = $this->dom(<<<'BLADE'
            <x-ui.field label="البريد" for="email" hint="نص مساعد" error="بريد غير صحيح" required>
                <x-ui.input type="email" name="email" />
            </x-ui.field>
            BLADE);

        $this->assertSame('email', $this->element($xpath, '//label')->getAttribute('for'));
        $input = $this->element($xpath, '//input');
        $this->assertSame('email', $input->getAttribute('id'));
        $this->assertSame('email-hint email-error', $input->getAttribute('aria-describedby'));
        $this->assertSame('true', $input->getAttribute('aria-invalid'));
        $this->assertTrue($input->hasAttribute('required'));
        $this->assertSame('نص مساعد', trim($this->element($xpath, '//*[@id="email-hint"]')->textContent));

        // The error is an icon plus text with a spoken prefix — never colour alone.
        $error = $this->element($xpath, '//*[@id="email-error"]');
        $this->assertSame(1, $this->countMatches($xpath, '//*[@id="email-error"]/*[local-name()="svg"]'));
        $this->assertStringContainsString(__('ui.error_prefix', [], 'ar'), $error->textContent);
        $this->assertStringContainsString('بريد غير صحيح', $error->textContent);
        // The required marker is visual only; the control carries `required` for assistive technology.
        $this->assertSame('true', $this->element($xpath, '//*['.self::cls('ui-field__required').']')->getAttribute('aria-hidden'));
    }

    public function test_a_valid_field_has_no_error_wiring(): void
    {
        $input = $this->element($this->dom('<x-ui.field label="x" for="name" :error="$e"><x-ui.input name="name" /></x-ui.field>', ['e' => '']), '//input');

        $this->assertFalse($input->hasAttribute('aria-invalid'));
        $this->assertFalse($input->hasAttribute('aria-describedby'));
        $this->assertFalse($input->hasAttribute('required'));
    }

    public function test_controls_outside_a_field_only_use_their_own_attributes(): void
    {
        $xpath = $this->dom(<<<'BLADE'
            <x-ui.fieldset legend="التاريخ" id="dob" hint="يوم / شهر / سنة" error="تاريخ غير صحيح">
                <x-ui.select id="dob-day" name="day" :options="[1 => '1']" aria-describedby="dob-hint" />
            </x-ui.fieldset>
            BLADE);

        $select = $this->element($xpath, '//select');
        $this->assertSame('dob-hint', $select->getAttribute('aria-describedby'));
        $this->assertFalse($select->hasAttribute('aria-invalid'));
        $fieldset = $this->element($xpath, '//fieldset');
        $this->assertSame('dob-hint dob-error', $fieldset->getAttribute('aria-describedby'));
        $this->assertSame('التاريخ', trim($this->element($xpath, '//fieldset/legend')->textContent));
    }

    public function test_select_and_textarea_share_the_field_wiring(): void
    {
        $xpath = $this->dom(<<<'BLADE'
            <x-ui.field label="الخيار" for="choice" error="اختر"><x-ui.select name="choice" placeholder :options="['a' => 'أ', 'b' => 'ب']" selected="b" /></x-ui.field>
            <x-ui.field label="ملاحظات" for="notes"><x-ui.textarea name="notes">نص</x-ui.textarea></x-ui.field>
            BLADE);

        $select = $this->element($xpath, '//select');
        $this->assertSame('true', $select->getAttribute('aria-invalid'));
        $this->assertSame('choice-error', $select->getAttribute('aria-describedby'));
        $this->assertSame(3, $this->countMatches($xpath, '//select/option'));
        $this->assertSame(__('ui.select_placeholder', [], 'ar'), trim($this->element($xpath, '//select/option[1]')->textContent));
        $this->assertSame('b', $this->element($xpath, '//select/option[@selected]')->getAttribute('value'));
        $this->assertSame('نص', $this->element($xpath, '//textarea[@id="notes"]')->textContent);
    }

    public function test_checkbox_radio_and_switch_are_native_inputs_inside_their_label(): void
    {
        $xpath = $this->dom(<<<'BLADE'
            <x-ui.checkbox name="terms" id="terms" label="أوافق" error="مطلوب" />
            <x-ui.radio name="size" value="1" label="خيار" />
            <x-ui.switch name="active" label="مفعّل" checked />
            BLADE);

        $checkbox = $this->element($xpath, '//input[@type="checkbox" and @name="terms"]');
        $this->assertSame('true', $checkbox->getAttribute('aria-invalid'));
        $this->assertSame('terms-error', $checkbox->getAttribute('aria-describedby'));
        $this->assertSame('label', $checkbox->parentNode?->nodeName);
        $this->assertSame(1, $this->countMatches($xpath, '//label/input[@type="radio" and @name="size"]'));
        $switch = $this->element($xpath, '//input[@name="active"]');
        $this->assertSame('switch', $switch->getAttribute('role'));
        $this->assertSame('checkbox', $switch->getAttribute('type'));
        $this->assertRenderFails('<x-ui.checkbox name="x" label="x" hint="needs an id" />', 'needs an id');
    }

    public function test_error_summary_links_every_message_to_its_field(): void
    {
        $xpath = $this->dom('<x-ui.error-summary :errors="$errors" id="form-errors" />', [
            'errors' => (new ViewErrorBag)->put('default', new MessageBag(['email' => ['بريد غير صحيح'], 'items.0.name' => ['مطلوب']])),
        ]);

        $summary = $this->element($xpath, '//*[@id="form-errors"]');
        $this->assertSame('alert', $summary->getAttribute('role'));
        $this->assertSame('-1', $summary->getAttribute('tabindex'));
        $this->assertTrue($summary->hasAttribute('autofocus'));
        $this->assertSame('form-errors-title', $summary->getAttribute('aria-labelledby'));
        $this->assertSame('#email', $this->element($xpath, '//li[1]/a')->getAttribute('href'));
        $this->assertSame('#items-0-name', $this->element($xpath, '//li[2]/a')->getAttribute('href'));
        $this->assertSame('', trim($this->render('<x-ui.error-summary :errors="[]" />')));
    }
}
