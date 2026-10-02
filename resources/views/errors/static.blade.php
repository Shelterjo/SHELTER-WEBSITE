{{-- Shared static error page (SI-S02): no database, no session, no view composer, so it renders even when those are
     what failed. Language from the URL prefix; bilingual elsewhere. $code picks the texts (site.errors.{code}_title /
     _text); $retry adds "Try again" (a reload of the same address). Used by 403, 419, 429, 4xx, 500, 503 and 5xx.
     $status = the real HTTP status for the 4xx/5xx fallbacks: the large code shows it, never a placeholder such as
     "4xx" (copy audit F02); without one, no code line is shown. --}}
@php
    $retry ??= true;
    $shownCode = isset($status) && is_int($status) ? (string) $status : (ctype_digit($code) ? $code : null);
    $prefix = request()->segment(1);
    $only = in_array($prefix, ['ar', 'en'], true) ? $prefix : null;
    $blocks = $only !== null ? [$only] : ['ar', 'en'];
    $first = $blocks[0];
@endphp
<!doctype html>
<html lang="{{ $first }}" dir="{{ $first === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('site.errors.'.$code.'_title', [], $first) }} — {{ __('site.title_brand', [], $first) }}</title>
    <link rel="icon" href="/brand/favicon-32.png" sizes="32x32" type="image/png">
    <meta name="theme-color" content="#131313">
    @vite(['resources/css/site.css'])
</head>
<body>
    <header class="ui-site-header ui-site-header--minimal">
        <div class="ui-container ui-site-header__inner">
            <a class="ui-site-header__brand" href="/{{ $first }}/">
                <img class="ui-site-header__logo" src="/brand/logo-white-240.png" srcset="/brand/logo-white-480.png 2x" width="240" height="88" alt="{{ __('site.brand') }}" lang="en">
            </a>
        </div>
    </header>
    <main id="main" class="ui-main">
        <div class="ui-page">
            <div class="ui-container">
                @if ($shownCode !== null)
                    <p class="ui-error__code" aria-hidden="true">{{ $shownCode }}</p>
                @endif
                @foreach ($blocks as $blockLocale)
                    <section class="ui-error" lang="{{ $blockLocale }}" dir="{{ $blockLocale === 'ar' ? 'rtl' : 'ltr' }}" aria-labelledby="error-{{ $blockLocale }}">
                        @if ($loop->first)
                            <h1 class="ui-error__title" id="error-{{ $blockLocale }}">{{ __('site.errors.'.$code.'_title', [], $blockLocale) }}</h1>
                        @else
                            <h2 class="ui-error__title" id="error-{{ $blockLocale }}">{{ __('site.errors.'.$code.'_title', [], $blockLocale) }}</h2>
                        @endif
                        <p class="ui-error__text">{{ __('site.errors.'.$code.'_text', [], $blockLocale) }}</p>
                        <ul class="ui-error__links" role="list">
                            @if ($retry)
                                <li><x-ui.button :href="request()->url()" icon="rotate-cw">{{ __('site.errors.retry', [], $blockLocale) }}</x-ui.button></li>
                            @endif
                            <li><x-ui.button :variant="$retry ? 'outline' : 'primary'" href="/{{ $blockLocale }}/">{{ __('site.nav.home', [], $blockLocale) }}</x-ui.button></li>
                        </ul>
                    </section>
                @endforeach
            </div>
        </div>
    </main>
</body>
</html>
