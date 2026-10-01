{{-- Empty state (DS-019 / M35 §20): says what is missing and what can be done next (actions slot). --}}
@props([
    'title',
    'icon' => 'inbox',
    'level' => 2,
])
@php
    $level = max(2, min(6, (int) $level));
@endphp
<div {{ $attributes->class('ui-empty-state') }}>
    <span class="ui-empty-state__icon"><x-ui.icon :name="$icon" size="lg" /></span>
    <h{{ $level }} class="ui-empty-state__title">{{ $title }}</h{{ $level }}>
    @if ($slot->isNotEmpty())
        <p class="ui-empty-state__text">{{ $slot }}</p>
    @endif
    @isset($actions)
        <div class="ui-cluster ui-empty-state__actions">{{ $actions }}</div>
    @endisset
</div>
