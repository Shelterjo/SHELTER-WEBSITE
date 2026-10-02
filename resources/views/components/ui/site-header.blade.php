{{--
    Site header (DS-018, NAV-005): the white brand logo (D-309) linking home, the primary navigation and the language
    switch. Same destinations on every width: from 360px Menu and Locations stay visible in the bar (NAV-004 — never
    only behind a menu button) and the menu button opens the navigation drawer, which holds every item and the
    language choice; from 1024px everything is inline and the button is not needed. `minimal` = logo only (root
    gateway, RG-02 / D-066). Items: [['label', 'href', 'current' => 'page'|'true'|null]]; languages: [['locale',
    'label', 'href', 'current' => bool]]. The drawer is a native dialog opened by Invoker Commands, with a fallback in
    resources/js/ui/dialog.ts; without JavaScript the links stay reachable.
    `search` = URL of the results page: a search field at the top of the drawer, and a search link in the bar from
    1024px where the drawer button is gone (GLOBAL-SEARCH §4) — the bar itself keeps Menu + Locations (D-027).
--}}
@props([
    'home',
    'nav' => [],
    'languages' => [],
    'minimal' => false,
    'drawerId' => 'site-nav',
    'search' => null,
])
@php
    $locale = app()->getLocale();
    $other = collect($languages)->first(fn (array $language): bool => ! $language['current']);
    $hasNav = ! $minimal && count($nav) > 0;
@endphp
<header {{ $attributes->class(['ui-site-header', 'ui-site-header--minimal' => $minimal]) }}>
    <div class="ui-container ui-site-header__inner">
        {{-- The logo image carries the brand name, so the link is named by its alt text. --}}
        <a class="ui-site-header__brand" href="{{ $home }}">
            <picture>
                <source type="image/webp" srcset="/brand/logo-white-240.webp 1x, /brand/logo-white-480.webp 2x">
                <img class="ui-site-header__logo" src="/brand/logo-white-240.png" srcset="/brand/logo-white-480.png 2x"
                    width="240" height="88" alt="{{ __('site.brand') }}" lang="en" fetchpriority="high">
            </picture>
        </a>
        @if ($hasNav)
            <nav class="ui-site-header__nav" aria-label="{{ __('ui.main_navigation') }}">
                <ul class="ui-site-header__list" role="list">
                    @foreach ($nav as $item)
                        <li><x-ui.nav-link :href="$item['href']" :current="$item['current'] ?? false">{{ $item['label'] }}</x-ui.nav-link></li>
                    @endforeach
                </ul>
            </nav>
        @endif
        @if ($other !== null || $hasNav)
            <div class="ui-site-header__end">
                @if ($other !== null && ! $minimal)
                    <a class="ui-site-header__lang" href="{{ $other['href'] }}" lang="{{ $other['locale'] }}" hreflang="{{ $other['locale'] }}" aria-label="{{ $other['label'] }}">
                        <span class="ui-site-header__lang-long">{{ $other['label'] }}</span>
                        <span class="ui-site-header__lang-short" aria-hidden="true">{{ $other['locale'] === 'ar' ? 'ع' : 'EN' }}</span>
                    </a>
                @endif
                @if (filled($search) && ! $minimal)
                    <x-ui.button variant="ghost" icon="search" icon-only :label="__('site.search.open')" :href="$search" class="ui-site-header__search" />
                @endif
                @if ($hasNav)
                    <x-ui.button variant="ghost" icon="menu" icon-only :label="__('ui.navigation.open')" :opens="$drawerId"
                        class="ui-site-header__menu" aria-controls="{{ $drawerId }}" aria-expanded="false" data-ui-nav-open />
                @endif
            </div>
        @endif
    </div>
    @if ($hasNav)
        <x-ui.drawer :id="$drawerId" side="end" :title="__('ui.navigation.title')" class="ui-nav-drawer" data-ui-nav-drawer>
            @if (filled($search))
                <x-ui.search-form :action="$search" :id="$drawerId.'-search'" :label="__('site.search.label')" compact class="ui-nav-drawer__search" />
            @endif
            <nav aria-label="{{ __('ui.main_navigation') }}">
                <ul class="ui-nav-drawer__list" role="list">
                    @foreach ($nav as $item)
                        <li class="ui-nav-drawer__item" data-ui-nav-item>
                            <a class="ui-nav-drawer__link" href="{{ $item['href'] }}" @if ($item['current'] ?? null) aria-current="{{ $item['current'] }}" @endif>
                                <span>{{ $item['label'] }}</span>
                                <x-ui.icon name="arrow-right" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
            @if (count($languages) > 1)
                <x-slot:footer>
                    <ul class="ui-nav-drawer__languages" role="list" aria-label="{{ __('ui.navigation.language') }}">
                        @foreach ($languages as $language)
                            <li>
                                <a class="ui-nav-drawer__language" href="{{ $language['href'] }}" lang="{{ $language['locale'] }}" hreflang="{{ $language['locale'] }}"
                                    @if ($language['current']) aria-current="true" @endif>{{ $language['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </x-slot:footer>
            @endif
        </x-ui.drawer>
    @endif
</header>
