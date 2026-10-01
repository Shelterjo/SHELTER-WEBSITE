{{--
    Text input (DS-015). Inside x-ui.field it takes its id, hint/error wiring and `required` from the field (@aware).
    Standalone it needs its own id and a label. 16px text, so the page never zooms on focus (iOS).
--}}
@aware([
    'for' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
])
@props([
    'type' => 'text',
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
<input {{ $controlAttributes->class('ui-input')->merge(['type' => $type]) }}>
