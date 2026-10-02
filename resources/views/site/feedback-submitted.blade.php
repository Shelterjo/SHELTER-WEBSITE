@extends('layouts.site')

@section('title', __('feedback.success.title').' — '.__('site.title_brand'))

@section('content')
    {{--
        VOICE-OF-CUSTOMER §2: the SAME screen for every rating — nothing here depends on the answer (no review gating, no
        Google review link). No reference number, nothing personal. Wording pending the Owner's final copy (PO-063).
    --}}
    <div class="ui-page">
        <div class="ui-container">
            <section class="ui-apply-done" aria-labelledby="done-title">
                <span class="ui-apply-done__icon"><x-ui.icon name="circle-check" size="lg" /></span>
                <h1 class="ui-apply-done__title" id="done-title" tabindex="-1" autofocus>{{ __('feedback.success.title') }}</h1>
                <p class="ui-apply-done__text">{{ __('feedback.success.text') }}</p>
                <div class="ui-apply-done__actions">
                    <x-ui.button :href="\App\Support\PageUrl::route('home')">{{ __('feedback.success.back') }}</x-ui.button>
                </div>
            </section>
        </div>
    </div>
@endsection
