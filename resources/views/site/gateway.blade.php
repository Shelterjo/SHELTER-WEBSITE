@extends('layouts.site')
{{-- Its Google description is the approved root line (D-332), when written. --}}
@php($description = filled($description ?? null) ? $description : (filled(__('site.gateway.lead')) ? __('site.gateway.lead') : null))
{{-- RG-02 / D-066: a very short header on / (the logo only); the root is not a third full copy of the site. --}}
@php($minimalHeader = true)

@section('title', __('site.brand').' · '.__('site.brand_ar'))

@section('content')
    {{-- Root gateway (D-052, D-066): the brand in both languages, the two language doors and, under each door, the
         two shortcuts D-066 asks for (menu and branches) in that language — nothing else. Its own
         wording is the Owner's (D-068 three options → approved line D-332), editable in dashboard → Site texts →
         site.gateway.lead; an empty line is not shown. Built from design-system parts only (FINAL-QA QA-020). --}}
    <div class="ui-page ui-gateway">
        <div class="ui-container">
            <header class="ui-page-intro ui-gateway__intro">
                <h1 class="ui-page-intro__title" lang="en" dir="ltr">{{ __('site.brand') }}</h1>
                <p class="ui-page-intro__lead" lang="ar" dir="rtl">{{ __('site.brand_ar') }}</p>
                @if (filled(__('site.gateway.lead')))
                    <p class="ui-page-intro__lead">{{ __('site.gateway.lead') }}</p>
                @endif
            </header>
            <nav aria-label="{{ __('site.choose_language') }} · Choose language">
                <ul class="ui-cluster ui-gateway__doors" role="list">
                    @foreach (['ar' => ['العربية', 'primary', 'rtl'], 'en' => ['English', 'outline', 'ltr']] as $door => [$doorName, $doorVariant, $doorDir])
                        <li class="ui-gateway__door" lang="{{ $door }}" dir="{{ $doorDir }}">
                            <x-ui.button size="lg" :variant="$doorVariant" :href="\App\Support\PageUrl::route('home', ['locale' => $door])" :hreflang="$door">{{ $doorName }}</x-ui.button>
                            <p class="ui-gateway__shortcuts">
                                <a href="{{ \App\Support\PageUrl::route('menu', ['locale' => $door, 'market' => 'jo']) }}">{{ __('site.nav.menu', [], $door) }}</a>
                                <span aria-hidden="true">·</span>
                                <a href="{{ \App\Support\PageUrl::route('locations', ['locale' => $door, 'market' => 'jo']) }}">{{ __('site.nav.locations', [], $door) }}</a>
                            </p>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </div>
@endsection
