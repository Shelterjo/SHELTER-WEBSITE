<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir ?? 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('site.brand'))</title>
    @unless (\App\Http\Middleware\Indexing::siteIndexable() && empty($noindex))
        <meta name="robots" content="noindex, nofollow">
    @endunless
    @isset($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endisset
    @foreach ($alternates ?? [] as $hreflang => $url)
        <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $url }}">
    @endforeach
    <link rel="icon" href="/brand/favicon-32.png" sizes="32x32" type="image/png">
    <link rel="apple-touch-icon" href="/brand/favicon-180.png">
    <meta name="theme-color" content="#131313">
    @vite(['resources/css/site.css', 'resources/js/site.ts'])
</head>
<body>
    <x-ui.skip-link :label="__('site.skip_to_content')" />
    @php($locale = app()->getLocale())
    @php($otherLocale = $locale === 'ar' ? 'en' : 'ar')
    <header class="ui-site-header">
        <div class="ui-container ui-site-header__inner">
            {{-- Brand logo from the old site (D-309). The image carries the brand name, so the link is named by its alt. --}}
            <a class="ui-site-header__brand" href="{{ \App\Support\PageUrl::route('home', ['locale' => $locale]) }}">
                <picture>
                    <source type="image/webp" srcset="/brand/logo-white-240.webp 1x, /brand/logo-white-480.webp 2x">
                    <img class="ui-site-header__logo" src="/brand/logo-white-240.png" srcset="/brand/logo-white-480.png 2x"
                        width="240" height="88" alt="{{ __('site.brand') }}" lang="en" fetchpriority="high">
                </picture>
            </a>
            @isset($alternates[$otherLocale])
                <x-ui.nav-link :href="$alternates[$otherLocale]" :lang="$otherLocale" :hreflang="$otherLocale">{{ __('site.switch_language') }}</x-ui.nav-link>
            @endisset
        </div>
    </header>
    <main id="main" tabindex="-1">
        @yield('content')
    </main>
</body>
</html>
