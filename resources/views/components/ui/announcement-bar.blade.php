{{--
    Announcement bar (DX-010, DYNAMIC-EXPERIENCE-ENGINE §3): one short line above the site header for the experience
    the engine picked — a title, an optional short text and an optional link. `urgent` (an urgent notice) uses the
    warning colours and its icon, never colour alone. Nothing to show → the caller does not render it (DX-012).
--}}
@props([
    'title',
    'text' => null,
    'href' => null,
    'link' => null,
    'urgent' => false,
    'label' => null,
])
<aside {{ $attributes->class(['ui-announcement', 'ui-announcement--urgent' => $urgent])->merge(['aria-label' => $label ?? __('ui.announcement')]) }}>
    <div class="ui-container ui-announcement__inner">
        <x-ui.icon :name="$urgent ? 'triangle-alert' : 'info'" size="sm" class="ui-announcement__icon" />
        <p class="ui-announcement__text">
            <strong>{{ $title }}</strong>
            @if (filled($text))
                <span>{{ $text }}</span>
            @endif
        </p>
        @if (filled($href) && filled($link))
            <a class="ui-announcement__link" href="{{ $href }}" @if (str_starts_with((string) $href, 'https://')) rel="noopener" @endif>
                <span>{{ $link }}</span>
                <x-ui.icon name="arrow-right" size="sm" />
            </a>
        @endif
    </div>
</aside>
