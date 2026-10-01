{{--
    Icon — Lucide only (DS-012 / M34 §6). Inline SVG from resources/icons (allow-list in scripts/icons.mjs, stroke 1.75
    from the tokens). Sizes sm 16 · md 20 · lg 24. Decorative (aria-hidden) unless a label is given. Directional icons
    (arrows, previous/next, breadcrumb separator, sign-out) mirror in RTL; search, close, status and logos never do.
--}}
@props([
    'name',
    'size' => 'md',
    'label' => null,
])
@php
    $directional = ['arrow-right', 'chevron-left', 'chevron-right', 'log-out'];
    if (! in_array($size, ['sm', 'md', 'lg'], true)) {
        throw new InvalidArgumentException("x-ui.icon: unknown size [{$size}] (sm · md · lg).");
    }
    $path = resource_path('icons/'.$name.'.svg');
    if (preg_match('/^[a-z0-9-]+$/', $name) !== 1 || ! is_file($path)) {
        throw new InvalidArgumentException("x-ui.icon: unknown icon [{$name}]. Add it to scripts/icons.mjs, then run npm run icons.");
    }
    $labelled = $label !== null && $label !== '';
    $iconAttributes = $attributes
        ->class([
            'ui-icon',
            'ui-icon--'.$size => $size !== 'md',
            'ui-icon--directional' => in_array($name, $directional, true),
        ])
        ->merge($labelled ? ['role' => 'img', 'aria-label' => $label] : ['aria-hidden' => 'true'])
        ->merge(['focusable' => 'false']);
    $svg = preg_replace('/^<svg\b/', '<svg '.$iconAttributes, trim((string) file_get_contents($path)), 1);
@endphp
{!! $svg !!}{{-- nosemgrep: shelter-blade-unescaped-output — allow-listed Lucide file from resources/icons; attributes escaped by the attribute bag --}}
