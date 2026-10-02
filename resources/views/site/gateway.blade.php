@extends('layouts.site')

@section('content')
    {{-- PHASE 1 shell. Gateway copy (3 options per text, D-068) and sections arrive in PHASE 2. --}}
    <div class="ui-container">
        <h1 lang="en" dir="ltr">{{ __('site.brand') }}</h1>
        @if (filled(__('site.gateway.lead')))
            <p>{{ __('site.gateway.lead') }}</p>
        @endif
        <nav aria-label="{{ __('site.choose_language') }}">
            <ul class="ui-cluster" role="list">
                <li><x-ui.nav-link :href="\App\Support\PageUrl::route('home', ['locale' => 'ar'])" lang="ar" hreflang="ar">العربية</x-ui.nav-link></li>
                <li><x-ui.nav-link :href="\App\Support\PageUrl::route('home', ['locale' => 'en'])" lang="en" hreflang="en" dir="ltr">English</x-ui.nav-link></li>
            </ul>
        </nav>
    </div>
@endsection
