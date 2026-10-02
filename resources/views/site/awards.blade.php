@extends('layouts.site')

@section('title', __('awards.title').' — '.__('site.brand'))

@section('content')
    {{-- SI-B14: verified awards only (Fact Registry — PO-032), newest first; each with its public source when given. --}}
    <div class="ui-page">
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title">{{ __('awards.title') }}</h1>
            </header>
            <div class="ui-award-grid">
                @foreach ($awards as $award)
                    @include('site.partials.award-card', ['award' => $award, 'full' => true])
                @endforeach
            </div>
        </div>
    </div>
@endsection
