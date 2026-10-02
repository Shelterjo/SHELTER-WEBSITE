@extends('layouts.dashboard')

@section('title', __('dashboard.awards.title'))

@section('content')
    {{-- Content → Awards: each award, its state on the site in plain words, and one click to edit. --}}
    @php
        $badges = ['live' => ['success', 'circle-check'], 'draft' => ['neutral', 'file-text'], 'unconfirmed' => ['warning', 'hand'], 'incomplete' => ['warning', 'triangle-alert'], 'archived' => ['neutral', 'inbox']];
        $ar = app()->getLocale() === 'ar';
    @endphp
    <x-ui.page-header :title="__('dashboard.awards.title')" :description="__('dashboard.awards.description')">
        <x-slot:actions>
            <x-ui.button :href="route('dashboard.awards.create')" icon="circle-check">{{ __('dashboard.awards.add') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <nav class="ui-filter-bar" aria-label="{{ __('dashboard.awards.title') }}">
        <x-ui.chip :href="route('dashboard.awards.index')" :current="! $archived">{{ __('dashboard.awards.show_current') }}</x-ui.chip>
        <x-ui.chip :href="route('dashboard.awards.index', ['show' => 'archived'])" :current="$archived">{{ __('dashboard.awards.show_archived') }} <bdi>({{ $archivedCount }})</bdi></x-ui.chip>
    </nav>

    @if ($rows === [])
        <x-ui.empty-state :title="__('dashboard.awards.empty')" icon="circle-check" />
    @else
        <ul class="ui-item-list" role="list">
            @foreach ($rows as $row)
                @php $a = $row['award']; @endphp
                <li class="ui-item-list__item">
                    <div class="ui-item-list__main">
                        <h2 class="ui-item-list__title"><a href="{{ route('dashboard.awards.edit', $a) }}">{{ ($ar ? $a->title_ar : $a->title_en) ?? $a->title_ar ?? $a->title_en ?? __('dashboard.awards.new_title') }}</a></h2>
                        <p class="ui-item-list__meta">
                            <x-ui.badge :variant="$badges[$row['state']][0]" :icon="$badges[$row['state']][1]">{{ __('dashboard.awards.states.'.$row['state']) }}</x-ui.badge>
                            <span><bdi>{{ $a->year }}</bdi></span>
                            @if (filled($ar ? $a->issuer_ar : $a->issuer_en))
                                <span>{{ $ar ? $a->issuer_ar : $a->issuer_en }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="ui-item-list__actions">
                        <x-ui.button size="sm" :href="route('dashboard.awards.edit', $a)" icon="file-text">{{ __('dashboard.edit') }}</x-ui.button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
