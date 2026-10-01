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
    @foreach ($jsonLd ?? [] as $structuredData)
        <script type="application/ld+json">{!! \App\Support\StructuredData::encode($structuredData) !!}</script>{{-- nosemgrep: shelter-blade-unescaped-output — JSON-LD from StructuredData::encode (JSON_HEX_TAG/AMP: cannot close the tag) --}}
    @endforeach
</head>
<body>
    <x-ui.skip-link :label="__('site.skip_to_content')" />
    <x-ui.site-header :home="$siteChrome['home']" :nav="$siteChrome['nav']" :languages="$siteChrome['languages']" :minimal="$minimalHeader ?? false" />
    <main id="main" class="ui-main" tabindex="-1">
        @yield('content')
    </main>
    <x-ui.site-footer :home="$siteChrome['home']" :nav="$siteChrome['nav']" :languages="$siteChrome['languages']"
        :phone="$siteChrome['phone']" :whatsapp="$siteChrome['whatsapp']" :contact="$siteChrome['contact']" />
</body>
</html>
