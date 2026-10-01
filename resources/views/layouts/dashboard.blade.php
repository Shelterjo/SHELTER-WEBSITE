<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · {{ __('dashboard.name') }}</title>
    @vite(['resources/css/dashboard.css', 'resources/js/dashboard.ts'])
</head>
<body class="ui-dash">
    <a class="ui-skip-link" href="#main">{{ __('dashboard.skip_to_content') }}</a>
    {{-- PHASE 1 shell. Navigation groups (FINAL-ARCHITECTURE-REVIEW §10) are filled module by module from PHASE 3. --}}
    <header class="ui-dash__topbar">
        <p class="ui-dash__brand" lang="en" dir="ltr">SHELTER COFFEE</p>
        <form method="post" action="{{ route('dashboard.logout') }}">
            @csrf
            <button type="submit" class="ui-btn ui-btn--ghost">{{ __('dashboard.auth.logout') }}</button>
        </form>
    </header>
    <main id="main" class="ui-dash__main" tabindex="-1">
        @yield('content')
    </main>
</body>
</html>
