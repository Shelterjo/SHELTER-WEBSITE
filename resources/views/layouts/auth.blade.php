<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · {{ __('dashboard.name') }}</title>
    @vite(['resources/css/dashboard.css', 'resources/js/dashboard.ts'])
</head>
<body class="ui-auth">
    <x-ui.skip-link :label="__('dashboard.skip_to_content')" />
    <main id="main" class="ui-auth__panel" tabindex="-1">
        <p class="ui-auth__brand" lang="en" dir="ltr">SHELTER COFFEE</p>
        <h1 class="ui-auth__title">@yield('title')</h1>
        @yield('content')
    </main>
</body>
</html>
