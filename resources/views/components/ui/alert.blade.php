{{--
    Alert: info · success · warning · danger (DS-019). Icon + hidden type label + title/text: the meaning never depends
    on colour. Danger is announced as an alert, the others as a status (override with role="…" when static).
--}}
@props([
    'variant' => 'info',
    'title' => null,
])
@php
    $icons = ['info' => 'info', 'success' => 'circle-check', 'warning' => 'triangle-alert', 'danger' => 'circle-alert'];
    if (! array_key_exists($variant, $icons)) {
        throw new InvalidArgumentException("x-ui.alert: unknown variant [{$variant}].");
    }
@endphp
<div {{ $attributes->class(['ui-alert', 'ui-alert--'.$variant])->merge(['role' => $variant === 'danger' ? 'alert' : 'status']) }}>
    <x-ui.icon :name="$icons[$variant]" />
    <div class="ui-alert__content">
        @if (filled($title))
            <p class="ui-alert__title"><span class="ui-visually-hidden">{{ __('ui.alert.'.$variant) }}:</span> {{ $title }}</p>
        @else
            <span class="ui-visually-hidden">{{ __('ui.alert.'.$variant) }}:</span>
        @endif
        <div class="ui-alert__text">{{ $slot }}</div>
    </div>
</div>
