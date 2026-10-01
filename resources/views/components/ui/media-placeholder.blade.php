{{--
    Internal media placeholder (M38: unapproved media is never public — MEDIA PENDING OWNER APPROVAL). Storybook and
    the dashboard only; public pages render no image box at all when there is no approved image.
--}}
@props([
    'label' => null,
])
<div {{ $attributes->class('ui-media-placeholder') }}>
    <x-ui.icon name="image" size="lg" />
    <span>{{ $label ?? __('ui.media_pending') }}</span>
</div>
