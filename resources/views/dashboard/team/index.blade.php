@extends('layouts.dashboard')

@section('title', __('dashboard.team.title'))

@section('content')
    {{-- Content → SHELTER Family: each profile, its state on the site in plain words, and one click to edit. --}}
    @php
        $badges = ['live' => ['success', 'circle-check'], 'hidden' => ['neutral', 'file-text'], 'incomplete' => ['warning', 'triangle-alert'], 'withdrawn' => ['danger', 'ban'], 'archived' => ['neutral', 'inbox']];
        $ar = app()->getLocale() === 'ar';
    @endphp
    <x-ui.page-header :title="__('dashboard.team.title')" :description="__('dashboard.team.description')">
        <x-slot:actions>
            <x-ui.button :href="route('dashboard.team.create')" icon="hand">{{ __('dashboard.team.add') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <nav class="ui-filter-bar" aria-label="{{ __('dashboard.team.title') }}">
        <x-ui.chip :href="route('dashboard.team.index')" :current="! $archived">{{ __('dashboard.awards.show_current') }}</x-ui.chip>
        <x-ui.chip :href="route('dashboard.team.index', ['show' => 'archived'])" :current="$archived">{{ __('dashboard.awards.show_archived') }} <bdi>({{ $archivedCount }})</bdi></x-ui.chip>
    </nav>

    @if ($rows === [])
        <x-ui.empty-state :title="__('dashboard.team.empty')" icon="hand" />
    @else
        <ul class="ui-item-list" role="list">
            @foreach ($rows as $row)
                @php $m = $row['member']; @endphp
                <li class="ui-item-list__item">
                    <div class="ui-item-list__main">
                        <h2 class="ui-item-list__title"><a href="{{ route('dashboard.team.edit', $m) }}">{{ ($ar ? $m->display_name_ar : $m->display_name_en) ?? $m->display_name_ar ?? $m->display_name_en ?? __('dashboard.team.new_title') }}</a></h2>
                        <p class="ui-item-list__meta">
                            <x-ui.badge :variant="$badges[$row['state']][0]" :icon="$badges[$row['state']][1]">{{ __('dashboard.team.states.'.$row['state']) }}</x-ui.badge>
                            @if (filled($ar ? $m->job_title_ar : $m->job_title_en))
                                <span>{{ $ar ? $m->job_title_ar : $m->job_title_en }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="ui-item-list__actions">
                        <x-ui.button size="sm" :href="route('dashboard.team.edit', $m)" icon="file-text">{{ __('dashboard.edit') }}</x-ui.button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
