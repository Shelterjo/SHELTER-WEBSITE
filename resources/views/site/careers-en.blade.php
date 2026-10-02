@extends('layouts.site')
@php($description = filled($description ?? null) ? $description : __('site.meta.careers'))

@section('title', __('careers.title').' — '.__('site.brand'))

@section('content')
    {{-- /en/careers/ (CAREERS-REQUIREMENTS §2): content in English; Apply leads to THE Arabic form — no English form. --}}
    <div class="ui-page">
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title">{{ __('careers.title') }}</h1>
                <p class="ui-page-intro__lead">{{ __('careers.lead') }}</p>
            </header>
            @if ($open)
                <p class="ui-careers__cta">
                    <x-ui.button size="lg" :href="$applyUrl" hreflang="ar" icon-end="arrow-right">{{ __('careers.apply') }}</x-ui.button>
                </p>
            @else
                <x-ui.alert>{{ __('careers.closed') }}</x-ui.alert>
            @endif
            <p class="ui-careers__track"><a href="{{ \App\Support\PageUrl::route('careers.track', ['locale' => 'ar']) }}" hreflang="ar">{{ __('careers.track_link') }}</a></p>
        </div>
    </div>
@endsection
