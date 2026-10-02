<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir ?? 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('site.brand'))</title>
    @if (! \App\Http\Middleware\Indexing::siteIndexable() || ! empty($noindex))
        <meta name="robots" content="noindex, nofollow">
    @elseif (! empty($noindexFollow))
        {{-- Search results: kept out of the index, links still followed (SEO-023). --}}
        <meta name="robots" content="noindex, follow">
    @endif
    @if (filled($description ?? null))
        <meta name="description" content="{{ $description }}">
    @endif
    @isset($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endisset
    @foreach ($alternates ?? [] as $hreflang => $url)
        <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $url }}">
    @endforeach
    @if (isset($canonical) && empty($noindex))
        {{-- Share previews (SEO-035; FINAL-QA QA-041): the page's own title, description and address. An image only when
             the page has an approved one (an event's image); the site-wide share image is the Owner's to approve. --}}
        @php $ogLocale = app()->getLocale() === 'ar' ? 'ar_AR' : 'en_US'; @endphp
        <meta property="og:site_name" content="{{ __('site.brand') }}">
        <meta property="og:type" content="{{ $ogType ?? 'website' }}">
        <meta property="og:title" content="{{ trim($__env->yieldContent('title', __('site.brand'))) }}">
        @if (filled($description ?? null))
            <meta property="og:description" content="{{ $description }}">
        @endif
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:locale" content="{{ $ogLocale }}">
        <meta property="og:locale:alternate" content="{{ $ogLocale === 'ar_AR' ? 'en_US' : 'ar_AR' }}">
        @if (filled($ogImage ?? null))
            <meta property="og:image" content="{{ $ogImage }}">
        @endif
        <meta name="twitter:card" content="{{ filled($ogImage ?? null) ? 'summary_large_image' : 'summary' }}">
    @endif
    <link rel="icon" href="/brand/favicon-32.png" sizes="32x32" type="image/png">
    <link rel="apple-touch-icon" href="/brand/favicon-180.png">
    <meta name="theme-color" content="#131313">
    @include('layouts._font-preload')
    @vite(['resources/css/site.css', 'resources/js/site.ts'])
    @foreach ($jsonLd ?? [] as $structuredData)
        <script type="application/ld+json">{!! \App\Support\StructuredData::encode($structuredData) !!}</script>{{-- nosemgrep: shelter-blade-unescaped-output — JSON-LD from StructuredData::encode (JSON_HEX_TAG/AMP: cannot close the tag) --}}
    @endforeach
</head>
<body>
    <x-ui.skip-link :label="__('site.skip_to_content')" />
    @if ($siteChrome['announcement'] ?? null)
        @php $notice = $siteChrome['announcement']; @endphp
        <x-ui.announcement-bar :title="$notice->title" :text="$notice->text" :href="$notice->ctaUrl" :link="$notice->ctaLabel" :urgent="$notice->urgent" data-experience="{{ $notice->id }}" />
    @endif
    <x-ui.site-header :home="$siteChrome['home']" :nav="$siteChrome['nav']" :languages="$siteChrome['languages']" :minimal="$minimalHeader ?? false" :search="$siteChrome['search']" />
    <main id="main" class="ui-main" tabindex="-1">
        @yield('content')
    </main>
    <x-ui.site-footer :home="$siteChrome['home']" :nav="$siteChrome['footerNav']" :languages="$siteChrome['languages']"
        :phone="$siteChrome['phone']" :whatsapp="$siteChrome['whatsapp']" :contact="$siteChrome['contact']" :social="$siteChrome['social']" :legal="$siteChrome['legal']" />
</body>
</html>
