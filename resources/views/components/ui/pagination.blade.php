{{--
    Pagination: previous / next always; numbered pages from 600px with first, last and the pages around the current
    one (gaps shown as "…"); below 600px a "page X of Y" summary. `url` = a pattern with {page} or a callable(int).
    Nothing is rendered for a single page.
--}}
@props([
    'current',
    'total',
    'url',
])
@php
    $current = max(1, min((int) $current, (int) $total));
    $total = (int) $total;
    $href = fn (int $page): string => is_callable($url) ? (string) $url($page) : str_replace('{page}', (string) $page, (string) $url);
    // First, last and current ± 1; a gap of exactly one page shows that page instead of "…".
    $wanted = array_values(array_unique(array_filter(
        [1, $current - 1, $current, $current + 1, $total],
        fn (int $page) => $page >= 1 && $page <= $total,
    )));
    sort($wanted);
    $items = [];
    foreach ($wanted as $index => $page) {
        $previous = $wanted[$index - 1] ?? null;
        if ($previous !== null && $page - $previous === 2) {
            $items[] = $page - 1;
        } elseif ($previous !== null && $page - $previous > 2) {
            $items[] = null;
        }
        $items[] = $page;
    }
@endphp
@if ($total > 1)
    <nav {{ $attributes->merge(['aria-label' => __('ui.pagination.label')]) }}>
        <ul class="ui-pagination__list">
            <li>
                @if ($current > 1)
                    <a class="ui-pagination__link" href="{{ $href($current - 1) }}" rel="prev">
                        <x-ui.icon name="chevron-left" size="sm" />
                        <span>{{ __('ui.pagination.previous') }}</span>
                    </a>
                @else
                    <a class="ui-pagination__link" role="link" aria-disabled="true">
                        <x-ui.icon name="chevron-left" size="sm" />
                        <span>{{ __('ui.pagination.previous') }}</span>
                    </a>
                @endif
            </li>
            @foreach ($items as $page)
                @if ($page === null)
                    <li class="ui-pagination__page ui-pagination__gap" aria-hidden="true">…</li>
                @else
                    <li class="ui-pagination__page">
                        <a class="ui-pagination__link" href="{{ $href($page) }}" aria-label="{{ __('ui.pagination.page', ['page' => $page]) }}" @if ($page === $current) aria-current="page" @endif>{{ $page }}</a>
                    </li>
                @endif
            @endforeach
            <li class="ui-pagination__summary">{{ __('ui.pagination.page_of', ['page' => $current, 'total' => $total]) }}</li>
            <li>
                @if ($current < $total)
                    <a class="ui-pagination__link" href="{{ $href($current + 1) }}" rel="next">
                        <span>{{ __('ui.pagination.next') }}</span>
                        <x-ui.icon name="chevron-right" size="sm" />
                    </a>
                @else
                    <a class="ui-pagination__link" role="link" aria-disabled="true">
                        <span>{{ __('ui.pagination.next') }}</span>
                        <x-ui.icon name="chevron-right" size="sm" />
                    </a>
                @endif
            </li>
        </ul>
    </nav>
@endif
