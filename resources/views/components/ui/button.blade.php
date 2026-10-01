{{--
    Button — the ONE button (DS-014 / M34 §2). Variants primary · secondary · outline · ghost · danger · link; sizes
    sm · md · lg; states hover · pressed · focus · disabled · loading. Renders <a> when href is given.
    Icon-only buttons must have an accessible name (label or aria-label). Loading keeps the label visible, adds a
    spinner, aria-busy and aria-disabled (the button keeps focus; resources/js/ui/aria-disabled.ts blocks activation).
    `opens` = id of a <dialog> to open (Invoker Commands, with a JS fallback in resources/js/ui/dialog.ts).
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'iconEnd' => null,
    'iconOnly' => false,
    'label' => null,
    'opens' => null,
])
@php
    if (! in_array($variant, ['primary', 'secondary', 'outline', 'ghost', 'danger', 'link'], true)) {
        throw new InvalidArgumentException("x-ui.button: unknown variant [{$variant}].");
    }
    if (! in_array($size, ['sm', 'md', 'lg'], true)) {
        throw new InvalidArgumentException("x-ui.button: unknown size [{$size}].");
    }
    $accessibleName = $label ?? $attributes->get('aria-label');
    if ($iconOnly && ($icon === null || $accessibleName === null || $accessibleName === '')) {
        throw new InvalidArgumentException('x-ui.button: an icon-only button needs an icon and an accessible name (label or aria-label).');
    }
    $isLink = $href !== null;
    $tag = $isLink ? 'a' : 'button';
    $iconSize = $size === 'lg' ? 'lg' : ($size === 'sm' ? 'sm' : 'md');
    $extra = [];
    if ($iconOnly) {
        $extra['aria-label'] = $accessibleName;
    }
    if ($isLink) {
        if ($disabled) {
            // A disabled link has no href (not activatable) but stays in the accessibility tree as a disabled link.
            $extra += ['role' => 'link', 'aria-disabled' => 'true'];
        } else {
            $extra['href'] = $href;
        }
    } else {
        $extra['type'] = $type;
        if ($disabled && ! $loading) {
            $extra['disabled'] = true;
        }
        if ($opens !== null) {
            $extra += ['commandfor' => $opens, 'command' => 'show-modal', 'aria-haspopup' => 'dialog', 'data-ui-dialog-open' => $opens];
        }
    }
    if ($loading) {
        $extra += ['aria-busy' => 'true', 'aria-disabled' => 'true'];
    }
    $buttonAttributes = $attributes->except(['aria-label'])->class([
        'ui-button',
        'ui-button--'.$variant,
        'ui-button--'.$size => $size !== 'md',
        'ui-button--icon-only' => $iconOnly,
        'ui-button--loading' => $loading,
    ])->merge($extra);
@endphp
<{{ $tag }} {{ $buttonAttributes }}>
    @if ($loading)
        <x-ui.icon name="loader-circle" :size="$iconSize" class="ui-button__spinner" />
    @elseif ($icon)
        <x-ui.icon :name="$icon" :size="$iconSize" />
    @endif
    @unless ($iconOnly)
        {{ $slot }}
    @endunless
    @if ($loading)
        <span class="ui-visually-hidden">{{ __('ui.loading') }}</span>
    @endif
    @if ($iconEnd && ! $iconOnly)
        <x-ui.icon :name="$iconEnd" :size="$iconSize" />
    @endif
</{{ $tag }}>
