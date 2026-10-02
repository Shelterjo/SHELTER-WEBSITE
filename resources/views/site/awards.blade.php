@extends('layouts.site')
@php($description = filled($description ?? null) ? $description : __('site.meta.awards'))

@section('title', __('awards.title').' — '.__('site.title_brand'))

@section('content')
    {{-- SI-B14: verified awards only (Fact Registry — PO-032), newest first; each with its public source when given. --}}
    <div class="ui-page">
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title">{{ __('awards.title') }}</h1>
                @if (filled(__('awards.lead')))
                    <p class="ui-page-intro__lead">{{ __('awards.lead') }}</p>
                @endif
            </header>
            <div class="ui-award-grid">
                @foreach ($awards as $award)
                    @include('site.partials.award-card', ['award' => $award, 'full' => true])
                @endforeach
            </div>
        </div>
    </div>
@endsection
