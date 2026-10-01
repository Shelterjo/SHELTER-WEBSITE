{{--
    Field — one layout for every form control (DS-015 / M34 §7): visible label · hint · error · control.
    The control inside the slot (x-ui.input / select / textarea) reads `for`, `hint`, `error` and `required` from this
    component (@aware) and wires id, aria-describedby and aria-invalid itself. The error is icon + text, never colour alone.
--}}
@props([
    'label',
    'for',
    'hint' => null,
    'error' => null,
    'required' => false,
    'optional' => false,
])
<div {{ $attributes->class('ui-field') }}>
    <label class="ui-field__label" for="{{ $for }}">
        {{ $label }}
        @if ($required)
            <span class="ui-field__required" aria-hidden="true">*</span>
        @elseif ($optional)
            <span class="ui-field__optional">({{ __('ui.optional') }})</span>
        @endif
    </label>
    @if (filled($hint))
        <p class="ui-field__hint" id="{{ $for }}-hint">{{ $hint }}</p>
    @endif
    @if (filled($error))
        <p class="ui-field__error" id="{{ $for }}-error">
            <x-ui.icon name="circle-alert" size="sm" />
            <span><span class="ui-visually-hidden">{{ __('ui.error_prefix') }}</span> {{ $error }}</span>
        </p>
    @endif
    {{ $slot }}
</div>
