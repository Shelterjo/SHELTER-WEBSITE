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
    @vite(['resources/css/site.css', 'resources/js/site.ts'])
</head>
<body>
    <x-ui.skip-link :label="__('site.skip_to_content')" />
    <main id="main" tabindex="-1">
        @yield('content')
    </main>
</body>
</html>
