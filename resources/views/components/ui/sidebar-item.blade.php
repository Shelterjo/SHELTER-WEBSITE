{{--
    Sidebar navigation item (dashboard): a list item around the shared nav link (DS-018) with an icon and an optional
    count. Use inside <nav aria-label> <ul class="ui-sidebar">.
--}}
@props([
    'href',
    'icon' => null,
    'current' => false,
    'count' => null,
])
<li {{ $attributes->class('ui-sidebar__item') }}>
    <a class="ui-nav-link ui-sidebar__link" href="{{ $href }}" @if ($current) aria-current="page" @endif>
        @if ($icon)
            <x-ui.icon :name="$icon" />
        @endif
        <span class="ui-sidebar__label">{{ $slot }}</span>
        @if ($count !== null)
            <span class="ui-sidebar__count"><bdi>{{ $count }}</bdi></span>
        @endif
    </a>
</li>
