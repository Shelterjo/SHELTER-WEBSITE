@extends('layouts.site')

@section('title', __('careers.success.title').' — '.__('site.title_brand'))

@section('content')
    {{-- CAREERS-REQUIREMENTS §5.2: received + the number (from the session, never the URL) + a way back. No email/WhatsApp. --}}
    <div class="ui-page">
        <div class="ui-container">
            <section class="ui-apply-done" aria-labelledby="done-title">
                <span class="ui-apply-done__icon"><x-ui.icon name="circle-check" size="lg" /></span>
                <h1 class="ui-apply-done__title" id="done-title" tabindex="-1" autofocus>{{ __('careers.success.title') }}</h1>
                <p class="ui-apply-done__label">{{ __('careers.success.number') }}</p>
                <p class="ui-apply-done__number" dir="ltr">{{ $number }}</p>
                <p class="ui-apply-done__text">{{ __('careers.success.text') }}</p>
                <div class="ui-apply-done__actions">
                    <x-ui.button :href="\App\Support\PageUrl::route('home')">{{ __('careers.success.back') }}</x-ui.button>
                    <x-ui.button variant="outline" :href="\App\Support\PageUrl::route('careers.track')">{{ __('careers.track.title') }}</x-ui.button>
                </div>
            </section>
        </div>
    </div>
@endsection
