{{--
    Navigation link shared by header, mobile nav, footer and the dashboard sidebar (DS-018). The current page is marked
    with aria-current plus a bold underline — never colour alone.
--}}
@props([
    'href',
    'current' => false,
    'icon' => null,
])
<a {{ $attributes->class('ui-nav-link')->merge(array_filter(['href' => $href, 'aria-current' => $current ? 'page' : null])) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" />
    @endif
    <span>{{ $slot }}</span>
</a>
