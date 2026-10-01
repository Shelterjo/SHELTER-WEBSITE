<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · {{ __('dashboard.name') }}</title>
    @vite(['resources/css/dashboard.css', 'resources/js/dashboard.ts'])
</head>
<body class="ui-shell">
    <x-ui.skip-link :label="__('dashboard.skip_to_content')" />
    <header class="ui-shell__topbar">
        <p class="ui-shell__brand" lang="en" dir="ltr">SHELTER COFFEE</p>
        <form method="post" action="{{ route('dashboard.logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost" icon="log-out">{{ __('dashboard.auth.logout') }}</x-ui.button>
        </form>
    </header>
    {{-- PHASE 1 shell. Navigation groups (FINAL-ARCHITECTURE-REVIEW §10) are filled module by module from PHASE 3. --}}
    <nav class="ui-shell__nav" aria-label="{{ __('ui.main_navigation') }}">
        <ul class="ui-sidebar">
            <x-ui.sidebar-item :href="route('dashboard.home')" icon="house" :current="request()->routeIs('dashboard.home')">{{ __('dashboard.command_center') }}</x-ui.sidebar-item>
        </ul>
    </nav>
    <main id="main" class="ui-shell__main" tabindex="-1">
        <div class="ui-shell__content">
            @yield('content')
        </div>
    </main>
</body>
</html>
