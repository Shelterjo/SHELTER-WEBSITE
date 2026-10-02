@extends('layouts.dashboard')

@section('title', __('dashboard.events.title'))

@section('content')
    {{-- Content → Events: on now and coming first, each with its state for visitors in plain words; one click to edit. --}}
    @php
        $E = 'dashboard.events.';
        $badges = [
            'live' => ['success', 'circle-check'], 'upcoming' => ['info', 'calendar'], 'draft' => ['neutral', 'file-text'],
            'paused' => ['warning', 'pause'], 'incomplete' => ['warning', 'triangle-alert'], 'ended' => ['neutral', 'clock'],
            'cancelled' => ['neutral', 'ban'], 'archived' => ['neutral', 'inbox'],
        ];
        $ar = app()->getLocale() === 'ar';
    @endphp
    <x-ui.page-header :title="__($E.'title')" :description="__($E.'description')">
        <x-slot:actions>
            <x-ui.button :href="route('dashboard.events.create')" icon="calendar">{{ __($E.'add') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <nav class="ui-filter-bar" aria-label="{{ __($E.'title') }}">
        @foreach (['current', 'past', 'archived'] as $key)
            <x-ui.chip :href="route('dashboard.events.index', $key === 'current' ? [] : ['show' => $key])" :current="$tab === $key">{{ __($E.'tabs.'.$key) }} <bdi>({{ $counts[$key] }})</bdi></x-ui.chip>
        @endforeach
    </nav>

    @if ($rows === [])
        <x-ui.empty-state :title="__($E.'empty.'.$tab)" icon="calendar">
            @if ($tab === 'current')
                {{ __($E.'empty_hint') }}
                <x-slot:actions>
                    <x-ui.button variant="secondary" :href="route('dashboard.events.create')" icon="calendar">{{ __($E.'add') }}</x-ui.button>
                </x-slot:actions>
            @endif
        </x-ui.empty-state>
    @else
        <ul class="ui-item-list" role="list">
            @foreach ($rows as $row)
                @php $e = $row['event']; @endphp
                <li class="ui-item-list__item">
                    <div class="ui-item-list__main">
                        <h2 class="ui-item-list__title"><a href="{{ route('dashboard.events.edit', $e) }}">{{ ($ar ? $e->title_ar : $e->title_en) ?? $e->title_ar ?? $e->title_en ?? __($E.'untitled') }}</a></h2>
                        <p class="ui-item-list__meta">
                            <x-ui.badge :variant="$badges[$row['state']][0]" :icon="$badges[$row['state']][1]">{{ __($E.'states.'.$row['state']) }}</x-ui.badge>
                            @if ($row['when'] !== null)
                                <span><bdi>{{ $row['when'] }}</bdi></span>
                            @endif
                        </p>
                    </div>
                    <div class="ui-item-list__actions">
                        <x-ui.button size="sm" variant="secondary" :href="route('dashboard.events.edit', $e)" icon="file-text">{{ __('dashboard.edit') }}</x-ui.button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
