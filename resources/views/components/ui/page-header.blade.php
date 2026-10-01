{{--
    Page header (dashboard): optional breadcrumb slot · the page H1 · optional description · actions slot. One H1 per page.
--}}
@props([
    'title',
    'description' => null,
])
<div {{ $attributes->class('ui-page-header') }}>
    @isset($breadcrumb)
        {{ $breadcrumb }}
    @endisset
    <div class="ui-page-header__main">
        <div class="ui-page-header__text">
            <h1 class="ui-page-header__title">{{ $title }}</h1>
            @if (filled($description))
                <p class="ui-page-header__description">{{ $description }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="ui-page-header__actions">{{ $actions }}</div>
        @endisset
    </div>
</div>
