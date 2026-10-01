{{--
    Segmented control (DS §8 navigation, Menu IA spec §9.1 — branch selector): 2–3 mutually exclusive options in one
    row. Each option is a 44px <button> with aria-pressed inside role="group" (activating an option applies it at once,
    so these are toggle buttons, not radios). The selected option is filled + bold, and a sliding indicator moves to it
    (transform only, motion token; instant with reduced motion). Four or more options → a button that opens a bottom
    sheet with the list instead (spec §9.1), same data.
    Options: value => label, or value => ['label' => …, 'lang' => …] for a label in the other language.
--}}
@props([
    'label',
    'options' => [],
    'selected' => null,
])
@php
    if (count($options) < 2 || count($options) > 3) {
        throw new InvalidArgumentException('x-ui.segmented: 2 or 3 options (4+ → bottom sheet list, Menu IA §9.1).');
    }
@endphp
<div {{ $attributes->class(['ui-segmented', 'ui-segmented--'.count($options)])->merge(['role' => 'group', 'aria-label' => $label]) }}>
    @foreach ($options as $value => $option)
        <button {{ (new \Illuminate\View\ComponentAttributeBag)->merge(array_filter([
            'type' => 'button',
            'class' => 'ui-segmented__option',
            'data-value' => (string) $value,
            'aria-pressed' => (string) $value === (string) $selected ? 'true' : 'false',
            'lang' => is_array($option) ? ($option['lang'] ?? null) : null,
        ], fn ($v) => $v !== null)) }}>{{ is_array($option) ? $option['label'] : $option }}</button>
    @endforeach
</div>
