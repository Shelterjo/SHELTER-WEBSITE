{{-- Badge: short status text — always text, never colour alone (DS-019). Variants neutral · success · warning · danger · info. --}}
@props([
    'variant' => 'neutral',
    'icon' => null,
])
@php
    if (! in_array($variant, ['neutral', 'success', 'warning', 'danger', 'info'], true)) {
        throw new InvalidArgumentException("x-ui.badge: unknown variant [{$variant}].");
    }
@endphp
<span {{ $attributes->class(['ui-badge', 'ui-badge--'.$variant => $variant !== 'neutral']) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" size="sm" />
    @endif
    {{ $slot }}
</span>
