@extends('layouts.site')

@section('content')
    {{-- PHASE 1 shell. Home sections (D-013 actions: menu, locations, branch info) arrive in PHASE 2. --}}
    <div class="ui-container">
        <h1>{{ __('site.brand') }}</h1>
        <p>
            <a href="{{ \App\Support\PageUrl::route('home', ['locale' => app()->getLocale() === 'ar' ? 'en' : 'ar']) }}"
               lang="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">{{ __('site.switch_language') }}</a>
        </p>
    </div>
@endsection
