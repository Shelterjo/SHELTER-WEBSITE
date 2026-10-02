<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · {{ __('dashboard.name') }}</title>
    @vite(['resources/css/dashboard.css', 'resources/js/dashboard.ts'])
</head>
<body class="ui-shell" data-ui-rendered-at="{{ now()->getTimestamp() }}">
    <x-ui.skip-link :label="__('dashboard.skip_to_content')" />
    <header class="ui-shell__topbar">
        <p class="ui-shell__brand" lang="en" dir="ltr">SHELTER COFFEE</p>
        <div class="ui-shell__actions">
            <x-ui.button variant="ghost" :href="url('/ar/')" icon="external-link" target="_blank" rel="noopener"><span class="ui-shell__action-label">{{ __('dashboard.view_site') }}</span></x-ui.button>
            <form method="post" action="{{ route('dashboard.logout') }}">
                @csrf
                <x-ui.button type="submit" variant="ghost" icon="log-out"><span class="ui-shell__action-label">{{ __('dashboard.auth.logout') }}</span></x-ui.button>
            </form>
        </div>
    </header>
    {{-- Navigation groups (FINAL-ARCHITECTURE-REVIEW §10) — items appear as their screens ship (DashboardChrome). --}}
    <nav class="ui-shell__nav" aria-label="{{ __('ui.main_navigation') }}">
        @foreach ($dashboardNav ?? [] as $group)
            @if ($group['label'] !== null)
                <p class="ui-shell__nav-group">{{ $group['label'] }}</p>
            @endif
            <ul class="ui-sidebar">
                @foreach ($group['links'] as $link)
                    <x-ui.sidebar-item :href="$link['href']" :icon="$link['icon']" :current="$link['current']">{{ $link['label'] }}</x-ui.sidebar-item>
                @endforeach
            </ul>
        @endforeach
    </nav>
    <main id="main" class="ui-shell__main" tabindex="-1">
        <div class="ui-shell__content">
            @if (session('status'))
                <x-ui.alert variant="success" class="ui-shell__flash">{{ session('status') }}</x-ui.alert>
            @endif
            @if (session('warning'))
                <x-ui.alert variant="warning" class="ui-shell__flash">{{ session('warning') }}</x-ui.alert>
            @endif
            @yield('content')
        </div>
    </main>
</body>
</html>
