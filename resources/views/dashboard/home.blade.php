@extends('layouts.dashboard')

@section('title', __('dashboard.command_center'))

@section('content')
    {{-- Command Center: what needs a look, as plain numbers that open their screen. --}}
    <x-ui.page-header :title="__('dashboard.command_center')" :description="__('dashboard.home.description')" />
    <div class="ui-tiles">
        @foreach ($tiles as $tile)
            <x-ui.stat-tile :label="$tile['label']" :value="$tile['value']" :href="$tile['href']" />
        @endforeach
    </div>
    @if ($attention !== [])
        <section aria-labelledby="home-attention">
            <h2 id="home-attention" class="ui-record__title">{{ __('dashboard.attention.title') }}</h2>
            @include('dashboard.partials.attention-list', ['items' => $attention])
        </section>
    @endif
@endsection
