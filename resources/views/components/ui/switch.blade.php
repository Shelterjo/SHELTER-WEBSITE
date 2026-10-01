{{--
    Switch (DS-015): a native checkbox with role="switch" — it submits with the form and works without JavaScript.
    On/off is shown by the thumb position and the filled track, not by colour alone.
--}}
@props([
    'label',
    'hint' => null,
])
@php
    $controlId = $attributes->get('id');
    if (filled($hint) && ! $controlId) {
        throw new InvalidArgumentException('x-ui.switch: a hint needs an id on the control.');
    }
@endphp
@if (filled($hint))
    <div class="ui-field">
@endif
<label class="ui-switch">
    <input {{ $attributes->class('ui-switch__input')->merge(array_filter([
        'type' => 'checkbox',
        'role' => 'switch',
        'aria-describedby' => filled($hint) ? $controlId.'-hint' : null,
    ])) }}>
    <span class="ui-switch__text">{{ $label }}</span>
</label>
@if (filled($hint))
    <p class="ui-field__hint" id="{{ $controlId }}-hint">{{ $hint }}</p>
    </div>
@endif
