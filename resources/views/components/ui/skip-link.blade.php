{{-- Skip link (WCAG 2.4.1): the first focusable element of every page, visible on focus. --}}
@props([
    'href' => '#main',
    'label' => null,
])
<a {{ $attributes->class('ui-skip-link')->merge(['href' => $href]) }}>{{ $label ?? __('ui.skip_to_content') }}</a>
