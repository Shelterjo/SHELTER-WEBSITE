{{-- Disclosure: native <details>/<summary> (keyboard + screen readers without JavaScript). FAQ items, optional help. --}}
@props([
    'summary',
    'open' => false,
])
<details {{ $attributes->class('ui-disclosure')->merge(['open' => (bool) $open]) }}>
    <summary class="ui-disclosure__summary">
        <x-ui.icon name="chevron-down" class="ui-disclosure__icon" />
        <span>{{ $summary }}</span>
    </summary>
    <div class="ui-disclosure__body">{{ $slot }}</div>
</details>
