@extends('layouts.site', ['noindex' => true])

@section('title', __('site.errors.404_title').' — '.__('site.brand'))

@section('content')
    {{-- SI-S01: language from the URL prefix (ErrorPageLocale), bilingual on any other path. Links: home · menu (when
         the page exists) · locations. --}}
    @php
        $market = app(\App\Services\Site\Markets::class)->current();
        $blocks = $bilingual ? ['ar', 'en'] : [$errorLocale];
    @endphp
    <div class="ui-page">
        <div class="ui-container">
            <p class="ui-error__code" aria-hidden="true">404</p>
            @foreach ($blocks as $blockLocale)
                @php
                    $parameters = ['locale' => $blockLocale, 'market' => $market?->code];
                    $links = array_filter([
                        __('site.nav.home', [], $blockLocale) => \App\Support\PageUrl::route('home', ['locale' => $blockLocale]),
                        __('site.nav.menu', [], $blockLocale) => $market ? \App\Support\SiteLinks::to('menu', $parameters) : null,
                        __('site.nav.locations', [], $blockLocale) => $market ? \App\Support\SiteLinks::to('locations', $parameters) : null,
                    ]);
                @endphp
                <section class="ui-error" lang="{{ $blockLocale }}" dir="{{ $blockLocale === 'ar' ? 'rtl' : 'ltr' }}" aria-labelledby="error-{{ $blockLocale }}">
                    @if ($loop->first)
                        <h1 class="ui-error__title" id="error-{{ $blockLocale }}">{{ __('site.errors.404_title', [], $blockLocale) }}</h1>
                    @else
                        <h2 class="ui-error__title" id="error-{{ $blockLocale }}">{{ __('site.errors.404_title', [], $blockLocale) }}</h2>
                    @endif
                    <p class="ui-error__text">{{ __('site.errors.404_text', [], $blockLocale) }}</p>
                    <nav aria-label="{{ __('site.errors.links', [], $blockLocale) }}">
                        <ul class="ui-error__links" role="list">
                            @foreach ($links as $label => $href)
                                <li><x-ui.button :variant="$loop->first ? 'primary' : 'outline'" :href="$href">{{ $label }}</x-ui.button></li>
                            @endforeach
                        </ul>
                    </nav>
                </section>
            @endforeach
        </div>
    </div>
@endsection
