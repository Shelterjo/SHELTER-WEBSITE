{{--
    Event card (DS-016): the card base with title · date · place · optional badge, media and summary. Dates and places
    come from approved event data (PHASE 4). With `href` the title link covers the card.
--}}
@props([
    'title',
    'date' => null,
    'datetime' => null,
    'place' => null,
    'badge' => null,
    'href' => null,
    'level' => 3,
])
@php
    $level = max(2, min(6, (int) $level));
@endphp
<article {{ $attributes->class(['ui-card', 'ui-event-card']) }}>
    @isset($media)
        <div class="ui-card__media">{{ $media }}</div>
    @endisset
    @if (filled($badge))
        <p class="ui-event-card__badge"><x-ui.badge>{{ $badge }}</x-ui.badge></p>
    @endif
    <h{{ $level }} class="ui-event-card__title">
        @if ($href)
            <a class="ui-stretched" href="{{ $href }}">{{ $title }}</a>
        @else
            {{ $title }}
        @endif
    </h{{ $level }}>
    @if (filled($date) || filled($place))
        <ul class="ui-event-card__meta" role="list">
            @if (filled($date))
                <li class="ui-card__meta">
                    <x-ui.icon name="calendar" size="sm" />
                    <span><span class="ui-visually-hidden">{{ __('ui.event.date') }}</span>
                        @if ($datetime)<time datetime="{{ $datetime }}">{{ $date }}</time>@else{{ $date }}@endif
                    </span>
                </li>
            @endif
            @if (filled($place))
                <li class="ui-card__meta">
                    <x-ui.icon name="map-pin" size="sm" />
                    <span><span class="ui-visually-hidden">{{ __('ui.event.place') }}</span> {{ $place }}</span>
                </li>
            @endif
        </ul>
    @endif
    @if ($slot->isNotEmpty())
        <div class="ui-card__body">{{ $slot }}</div>
    @endif
</article>
