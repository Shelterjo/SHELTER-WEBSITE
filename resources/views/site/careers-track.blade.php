@extends('layouts.site')

@section('title', __('careers.track.title').' — '.__('site.brand'))

@section('content')
    {{--
        Tracking without an account (RECRUITMENT-SECURITY §7): number + phone by POST (nothing in the URL), one generic
        answer for any mismatch, rate limits with a growing cooldown, and the PUBLIC status only.
    --}}
    <div class="ui-page">
        <div class="ui-container">
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title">{{ __('careers.track.title') }}</h1>
                <p class="ui-page-intro__lead">{{ __('careers.track.lead') }}</p>
            </header>
            <div class="ui-careers-track">
                @if ($status !== null)
                    <div class="ui-careers-track__result" role="status">
                        <p class="ui-careers-track__label">{{ __('careers.track.status') }}</p>
                        <p class="ui-careers-track__status">{{ __('careers.track.statuses.'.$status) }}</p>
                    </div>
                @endif
                @if ($error !== null)
                    <x-ui.alert variant="danger" role="alert">{{ $error }}</x-ui.alert>
                @endif
                <form class="ui-careers-track__form" method="post" action="{{ \App\Support\PageUrl::route('careers.track') }}" novalidate>
                    @csrf
                    <x-ui.field :label="__('careers.track.number')" for="number">
                        <x-ui.input id="number" name="number" dir="ltr" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="20" placeholder="JOB-2026-00101" />
                    </x-ui.field>
                    <x-ui.field :label="__('careers.track.phone')" for="track-phone">
                        <x-ui.input id="track-phone" name="phone" type="tel" dir="ltr" autocomplete="tel" inputmode="tel" maxlength="40" />
                    </x-ui.field>
                    <x-ui.button type="submit">{{ __('careers.track.submit') }}</x-ui.button>
                </form>
            </div>
        </div>
    </div>
@endsection
