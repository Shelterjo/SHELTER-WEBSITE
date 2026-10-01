{{--
    Open state of a branch (HOURS-009…012): a shape + text, never colour alone — filled dot = open, half = closing
    soon, ring = closed. `timeline` = App\Services\Site\StatusTimeline (server-computed in the market timezone); the
    browser keeps it current (resources/js/ui/open-status.ts: next segment on time, "closes in N min" each minute) and
    hides it once the known horizon has passed. Sizes md · lg.
--}}
@props([
    'timeline',
    'size' => 'md',
])
@php
    if (! in_array($size, ['md', 'lg'], true)) {
        throw new InvalidArgumentException("x-ui.open-status: unknown size [{$size}].");
    }
@endphp
<p {{ $attributes->class(['ui-open-status', 'ui-open-status--lg' => $size === 'lg'])->merge([
    'data-state' => $timeline->state(),
    'data-ui-open-status' => $timeline->toJson(),
]) }}>
    <span class="ui-open-status__mark" aria-hidden="true"></span>
    <span class="ui-open-status__text">{{ $timeline->text() }}</span>
</p>
