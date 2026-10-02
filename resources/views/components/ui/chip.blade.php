{{--
    Chip: filter / category choice (pill radius is for chips only — DS §4). A link marks the current choice with
    aria-current; a toggle button uses aria-pressed and shows a check icon when pressed (not colour alone).
    `toggle` false: a one-tap action chip (a suggested question) — no pressed state, no check icon.
--}}
@props([
    'href' => null,
    'current' => false,
    'pressed' => false,
    'disabled' => false,
    'toggle' => true,
])
@if ($href !== null)
    <a {{ $attributes->class('ui-chip')->merge(array_filter([
        'href' => $disabled ? null : $href,
        'role' => $disabled ? 'link' : null,
        'aria-disabled' => $disabled ? 'true' : null,
        'aria-current' => $current ? 'true' : null,
    ])) }}>{{ $slot }}</a>
@elseif (! $toggle)
    <button {{ $attributes->class('ui-chip')->merge(['type' => 'button', 'disabled' => $disabled]) }}>{{ $slot }}</button>
@else
    <button {{ $attributes->class('ui-chip')->merge([
        'type' => 'button',
        'aria-pressed' => $pressed ? 'true' : 'false',
        'disabled' => $disabled,
    ]) }}>
        <x-ui.icon name="check" size="sm" class="ui-chip__check" />
        {{ $slot }}
    </button>
@endif
