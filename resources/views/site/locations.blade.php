@extends('layouts.site')
@php($description = filled($description ?? null) ? $description : __('site.meta.locations'))

@section('title', __('site.titles.locations'))

@section('content')
    <div class="ui-page">
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title">{{ __('site.locations.title') }}</h1>
                <p class="ui-page-intro__lead">{{ __('site.locations.lead') }}</p>
            </header>
            @if (count($branches) > 0)
                <ul class="ui-panel-grid" role="list">
                    @foreach ($branches as $branch)
                        <li data-ui-reveal><x-ui.branch-card :branch="$branch" variant="panel" :level="2" :details-label="__('site.locations.details')" /></li>
                    @endforeach
                </ul>
                <p class="ui-note">{{ __('site.hours.timezone', ['market' => $market->name()]) }}</p>
            @else
                <x-ui.empty-state :title="__('site.locations.empty')" />
            @endif
        </div>
    </div>
@endsection
