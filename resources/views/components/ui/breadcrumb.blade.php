{{--
    Breadcrumb: an ordered list; the separators are directional icons (they mirror in RTL); the last item is the current
    page (plain text, aria-current). `items` = [['label' => …, 'href' => …], …].
--}}
@props([
    'items',
])
<nav {{ $attributes->class('ui-breadcrumb')->merge(['aria-label' => __('ui.breadcrumb')]) }}>
    <ol class="ui-breadcrumb__list">
        @foreach ($items as $item)
            <li class="ui-breadcrumb__item">
                @if ($loop->last)
                    <span class="ui-breadcrumb__current" aria-current="page">{{ $item['label'] }}</span>
                @else
                    <a class="ui-breadcrumb__link" href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                    <x-ui.icon name="chevron-right" size="sm" />
                @endif
            </li>
        @endforeach
    </ol>
</nav>
