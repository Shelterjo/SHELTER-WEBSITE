{{--
    Card — one base for every card (DS-016 / M34 §8): same surface, border, radius, shadow and spacing. Product, event
    and stat cards are content variants of this base. With `href` the title link stretches over the whole card
    (one tab stop, no nested interactives). Named slots: media (rendered only when given), footer.
--}}
@props([
    'as' => 'article',
    'title' => null,
    'level' => 3,
    'href' => null,
    'raised' => false,
])
@php
    if (! in_array($as, ['article', 'section', 'div', 'li'], true)) {
        throw new InvalidArgumentException("x-ui.card: unsupported element [{$as}].");
    }
    $level = max(2, min(6, (int) $level));
@endphp
<{{ $as }} {{ $attributes->class(['ui-card', 'ui-card--raised' => $raised]) }}>
    @isset($media)
        <div class="ui-card__media">{{ $media }}</div>
    @endisset
    @if (filled($title))
        <h{{ $level }} class="ui-card__title">
            @if ($href)
                <a class="ui-stretched" href="{{ $href }}">{{ $title }}</a>
            @else
                {{ $title }}
            @endif
        </h{{ $level }}>
    @endif
    @if ($slot->isNotEmpty())
        <div class="ui-card__body">{{ $slot }}</div>
    @endif
    @isset($footer)
        <div class="ui-card__footer">{{ $footer }}</div>
    @endisset
</{{ $as }}>
