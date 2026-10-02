@extends('layouts.site')

@section('title', __('franchise.success.title').' — '.__('site.brand'))

@section('content')
    {{-- docs/franchise/02 §5: the Owner's thank-you wording + the FR number (from the session, never the URL). --}}
    <div class="ui-page">
        <div class="ui-container">
            <section class="ui-apply-done" aria-labelledby="done-title">
                <span class="ui-apply-done__icon"><x-ui.icon name="circle-check" size="lg" /></span>
                <h1 class="ui-apply-done__title" id="done-title" tabindex="-1" autofocus>{{ __('franchise.success.title') }}</h1>
                <p class="ui-apply-done__label">{{ __('franchise.success.number') }}</p>
                <p class="ui-apply-done__number" dir="ltr">{{ $number }}</p>
                <p class="ui-apply-done__text">{{ __('franchise.success.text') }}</p>
                <div class="ui-apply-done__actions">
                    <x-ui.button :href="\App\Support\PageUrl::route('home')">{{ __('franchise.success.back') }}</x-ui.button>
                </div>
            </section>
        </div>
    </div>
@endsection
