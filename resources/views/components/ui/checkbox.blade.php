{{--
    Checkbox (DS-015): native input (keyboard + screen readers for free); the whole label row is the ≥44px touch target.
    `hint` / `error` need an id (they are linked with aria-describedby). Radios use x-ui.radio (same markup).
--}}
@props([
    'label',
    'type' => 'checkbox',
    'hint' => null,
    'error' => null,
])
@php
    if (! in_array($type, ['checkbox', 'radio'], true)) {
        throw new InvalidArgumentException("x-ui.checkbox: unknown type [{$type}].");
    }
    $controlId = $attributes->get('id');
    if ((filled($hint) || filled($error)) && ! $controlId) {
        throw new InvalidArgumentException('x-ui.checkbox: a hint or an error needs an id on the control.');
    }
    $describedBy = implode(' ', array_filter([
        filled($hint) ? $controlId.'-hint' : null,
        filled($error) ? $controlId.'-error' : null,
        $attributes->get('aria-describedby'),
    ])) ?: null;
    $checkAttributes = $attributes->except(['aria-describedby'])->class('ui-check__input')->merge(array_filter([
        'type' => $type,
        'aria-describedby' => $describedBy,
        'aria-invalid' => filled($error) ? 'true' : null,
    ], fn ($value) => $value !== null));
@endphp
@if (filled($hint) || filled($error))
    <div class="ui-field">
@endif
<label class="ui-check">
    <input {{ $checkAttributes }}>
    <span class="ui-check__text">{{ $label }}</span>
</label>
@if (filled($hint))
    <p class="ui-field__hint" id="{{ $controlId }}-hint">{{ $hint }}</p>
@endif
@if (filled($error))
    <p class="ui-field__error" id="{{ $controlId }}-error">
        <x-ui.icon name="circle-alert" size="sm" />
        <span><span class="ui-visually-hidden">{{ __('ui.error_prefix') }}</span> {{ $error }}</span>
    </p>
@endif
@if (filled($hint) || filled($error))
    </div>
@endif
