@extends('layouts.dashboard')

@section('title', $query !== '' ? __('dashboard.search.for', ['query' => $query]) : __('dashboard.search.title'))

@section('content')
    {{--
        Dashboard search (DASH-019): the GET form, then the results grouped by area — each one a full-width link to its
        screen. Without JavaScript the button submits; with it, the results area refreshes while typing (owner-search.ts).
    --}}
    @php $S = 'dashboard.search.'; @endphp
    <x-ui.page-header :title="__($S.'title')" :description="__($S.'description')" />

    <div class="ui-owner-search" data-owner-search>
        <x-ui.search-form :action="route('dashboard.search')" id="owner-search" :label="__($S.'field')" :value="$query" :submit-label="__($S.'submit')" class="ui-search-page__form" />
        <p class="ui-note">{{ __($S.'hint') }} {{ __($S.'privacy') }}</p>

        <div data-owner-search-results>
            <p class="ui-search-page__count" aria-live="polite">{{ $countText ?? ($query !== '' ? __($S.'short') : '') }}</p>

            @if ($ready && $groups === [])
                <x-ui.empty-state icon="search" :title="__($S.'none_title', ['query' => $query])">{{ __($S.'none_text') }}</x-ui.empty-state>
            @elseif ($groups !== [])
                <div class="ui-search-page__groups">
                    @foreach ($groups as $group => $found)
                        <section class="ui-search-page__group" aria-labelledby="owner-search-{{ $group }}">
                            <h2 class="ui-search-page__group-title" id="owner-search-{{ $group }}">{{ __($S.'groups.'.$group) }} <bdi>({{ $found['total'] }})</bdi></h2>
                            <ul class="ui-search-page__list" role="list">
                                @foreach ($found['hits'] as $hit)
                                    <li class="ui-search-page__item">
                                        <a class="ui-search-page__link" href="{{ $hit['url'] }}">
                                            <span class="ui-search-page__title" @if ($hit['lang']) lang="{{ $hit['lang'] }}" @endif>{{ $hit['title'] }}</span>
                                            @if (filled($hit['meta']))
                                                <span class="ui-search-page__meta">{{ $hit['meta'] }}</span>
                                            @endif
                                        </a>
                                        <x-ui.icon name="arrow-right" size="sm" class="ui-search-page__go" />
                                    </li>
                                @endforeach
                            </ul>
                            @if ($found['more'] !== null)
                                <p class="ui-owner-search__more"><a href="{{ $found['more'] }}">{{ __($S.'more', ['count' => $found['total'] - count($found['hits'])]) }}</a></p>
                            @endif
                        </section>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
