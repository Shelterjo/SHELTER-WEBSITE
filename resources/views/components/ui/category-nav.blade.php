{{--
    Category bar (DS §8 navigation, Menu IA spec §2, F-16, R-06): plain in-page links (they work without JavaScript),
    shown as chips in one horizontally scrolling row; the current one carries aria-current="true". `sidebar` turns the
    same list into a vertical sticky sidebar from 1024px (spec §2 desktop) — one list, never two copies. The `start`
    slot holds the compact search button, the `end` slot the "all categories" button; both are mobile-only in the
    sidebar variant. Items: ['href', 'label', 'lang' (optional), 'current' (optional), 'attributes' (optional), 'hidden' (optional)].
--}}
@props([
    'label',
    'items' => [],
    'sidebar' => false,
])
<nav {{ $attributes->class(['ui-category-nav', 'ui-category-nav--sidebar' => $sidebar])->merge(['aria-label' => $label]) }}>
    @isset($start)
        <div class="ui-category-nav__start">{{ $start }}</div>
    @endisset
    <ul class="ui-category-nav__list" role="list">
        @foreach ($items as $item)
            @php
                // Extra attributes per chip (lang, data-*), built here so the tag below stays plain.
                $chipAttributes = new \Illuminate\View\ComponentAttributeBag(array_filter(['lang' => $item['lang'] ?? null] + ($item['attributes'] ?? []), fn ($v) => $v !== null));
            @endphp
            <li @if (! empty($item['hidden'])) hidden @endif>
                <x-ui.chip :href="$item['href']" :current="(bool) ($item['current'] ?? false)" :attributes="$chipAttributes">{{ $item['label'] }}</x-ui.chip>
            </li>
        @endforeach
    </ul>
    @isset($end)
        <div class="ui-category-nav__end">{{ $end }}</div>
    @endisset
</nav>
