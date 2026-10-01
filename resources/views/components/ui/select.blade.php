{{--
    Select (DS-015): the native element (keyboard, screen readers and mobile pickers for free) in the shared control box,
    with a token-sized chevron. Options from `options` (value => label) or from the slot. `placeholder` adds an empty
    first option (not selectable once a value is chosen).
--}}
@aware([
    'for' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
])
@props([
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'invalid' => false,
])
@php
    // Wiring shared by input / select / textarea. Inside x-ui.field (its `for` = this id) the control takes the field's
    // hint, error and required state; anywhere else (e.g. inside x-ui.fieldset) only its own attributes count.
    $controlId = $attributes->get('id', $for);
    $wired = $for !== null && $controlId === $for;
    $describedBy = implode(' ', array_filter([
        $wired && filled($hint) ? $for.'-hint' : null,
        $wired && filled($error) ? $for.'-error' : null,
        $attributes->get('aria-describedby'),
    ])) ?: null;
    $isInvalid = $invalid || ($wired && filled($error));
    $controlAttributes = $attributes->except(['id', 'aria-describedby', 'for', 'hint', 'error'])->merge(array_filter([
        'id' => $controlId,
        'aria-describedby' => $describedBy,
        'aria-invalid' => $isInvalid ? 'true' : null,
        'required' => $wired && $required ? true : null,
    ], fn ($value) => $value !== null));
@endphp
<div class="ui-select">
    <select {{ $controlAttributes->class('ui-select__control') }}>
        @if ($placeholder !== null)
            <option value="" @selected($selected === null || $selected === '')>{{ $placeholder === true ? __('ui.select_placeholder') : $placeholder }}</option>
        @endif
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected($selected !== null && (string) $selected === (string) $value)>{{ $text }}</option>
        @endforeach
        {{ $slot }}
    </select>
    <x-ui.icon name="chevron-down" class="ui-select__icon" />
</div>
