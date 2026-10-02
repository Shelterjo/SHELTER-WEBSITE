{{--
    Stat tile (dashboard metric card, DS-016): label · value · optional trend (icon + text, never colour alone) · hint.
    Built on the card base; with `href` the whole tile links to the detail screen. No value yet (`value` null): a small
    muted dash, with `empty` as the words screen readers hear — never a sentence at number size (UX-006 DR-54).
--}}
@props([
    'label',
    'value',
    'empty' => null,
    'trend' => null,
    'trendText' => null,
    'hint' => null,
    'href' => null,
])
@php
    if ($trend !== null && ! in_array($trend, ['up', 'down'], true)) {
        throw new InvalidArgumentException("x-ui.stat-tile: unknown trend [{$trend}] (up · down).");
    }
@endphp
<div {{ $attributes->class(['ui-card', 'ui-stat-tile']) }}>
    <p class="ui-stat-tile__label">
        @if ($href)
            <a class="ui-stretched" href="{{ $href }}">{{ $label }}</a>
        @else
            {{ $label }}
        @endif
    </p>
    @if ($value === null)
        <p class="ui-stat-tile__value ui-stat-tile__value--empty">
            @if (filled($empty))
                <span aria-hidden="true">—</span><span class="ui-visually-hidden">{{ $empty }}</span>
            @else
                —
            @endif
        </p>
    @else
        <p class="ui-stat-tile__value"><bdi>{{ $value }}</bdi></p>
    @endif
    @if ($trend !== null && filled($trendText))
        <p class="ui-stat-tile__trend">
            <x-ui.icon :name="$trend === 'up' ? 'trending-up' : 'trending-down'" size="sm" />
            <span><span class="ui-visually-hidden">{{ __('ui.trend.'.$trend) }}</span> {{ $trendText }}</span>
        </p>
    @endif
    @if (filled($hint))
        <p class="ui-stat-tile__hint">{{ $hint }}</p>
    @endif
</div>
