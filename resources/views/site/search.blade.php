@extends('layouts.site')

@section('title', ($query !== '' ? __('site.search.for', ['query' => $query]) : __('site.search.title')).' — '.__('site.title_brand'))

@section('content')
    {{-- SI-B08: server-rendered results (no JavaScript needed), grouped by kind; the count is announced politely. --}}
    @php
        $market = app(\App\Services\Site\Markets::class)->current();
        $parameters = ['locale' => app()->getLocale(), 'market' => $market?->code];
        $starts = array_filter([
            __('site.nav.home') => \App\Support\PageUrl::route('home'),
            __('site.nav.menu') => $market ? \App\Support\SiteLinks::to('menu', $parameters) : null,
            __('site.nav.locations') => $market ? \App\Support\SiteLinks::to('locations', $parameters) : null,
        ]);
    @endphp
    <div class="ui-page">
        <div class="ui-container">
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title">{{ __('site.search.title') }}</h1>
                <p class="ui-page-intro__lead">{{ __('site.search.lead') }}</p>
            </header>
            <x-ui.search-form :action="$canonical" id="site-search" :label="__('site.search.label')" :value="$query" class="ui-search-page__form" />

            <p class="ui-search-page__count" aria-live="polite" @if ($results === null) hidden @endif>{{ $countText }}</p>

            @if ($results !== null && $results->total === 0)
                <x-ui.empty-state icon="search" :title="__('site.search.none_title', ['query' => $query])">
                    {{ __('site.search.none_text') }}
                    <x-slot:actions>
                        @foreach ($starts as $label => $href)
                            <x-ui.button :variant="$loop->first ? 'primary' : 'outline'" :href="$href">{{ $label }}</x-ui.button>
                        @endforeach
                    </x-slot:actions>
                </x-ui.empty-state>
            @elseif ($results !== null)
                <div class="ui-search-page__groups">
                    @foreach ($results->groups as $group => $hits)
                        <section class="ui-search-page__group" aria-labelledby="search-group-{{ $group }}">
                            <h2 class="ui-search-page__group-title" id="search-group-{{ $group }}">{{ __('site.search.groups.'.$group) }}</h2>
                            <ul class="ui-search-page__list" role="list">
                                @foreach ($hits as $hit)
                                    <li class="ui-search-page__item">
                                        <a class="ui-search-page__link" href="{{ $hit->url }}">
                                            <span class="ui-search-page__title" @if ($hit->titleLang) lang="{{ $hit->titleLang }}" dir="{{ \App\Support\Bidi::dir($hit->titleLang) }}" @endif>{{ $hit->title }}</span>
                                            @if ($hit->meta !== null)
                                                <span class="ui-search-page__meta" @if ($hit->metaLang) lang="{{ $hit->metaLang }}" dir="{{ \App\Support\Bidi::dir($hit->metaLang) }}" @endif>{{ $hit->meta }}</span>
                                            @endif
                                        </a>
                                        <x-ui.icon name="arrow-right" size="sm" class="ui-search-page__go" />
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
