@extends('layouts.dashboard')

@section('title', __('dashboard.recognition.title'))

@section('content')
    {{-- Content → Employee of the Month: each month's recognition, its state on the site in plain words; one click to edit. --}}
    @php
        $R = 'dashboard.recognition.';
        $badges = [
            'active' => ['success', 'circle-check'], 'not_shown' => ['warning', 'triangle-alert'], 'scheduled' => ['info', 'calendar'],
            'draft' => ['neutral', 'file-text'], 'expired' => ['neutral', 'clock'], 'archived' => ['neutral', 'inbox'],
        ];
        $ar = app()->getLocale() === 'ar';
    @endphp
    <x-ui.page-header :title="__($R.'title')" :description="__($R.'description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.team.index')" icon="hand">{{ __('dashboard.team.title') }}</x-ui.button>
            <x-ui.button :href="route('dashboard.recognition.create')" icon="award">{{ __($R.'add') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <nav class="ui-filter-bar" aria-label="{{ __($R.'title') }}">
        @foreach (['current', 'past', 'archived'] as $key)
            <x-ui.chip :href="route('dashboard.recognition.index', $key === 'current' ? [] : ['show' => $key])" :current="$tab === $key">{{ __($R.'tabs.'.$key) }} <bdi>({{ $counts[$key] }})</bdi></x-ui.chip>
        @endforeach
    </nav>

    @if ($rows === [])
        <x-ui.empty-state :title="__($R.'empty')" icon="award">
            @if ($tab === 'current')
                {{ __($R.'empty_hint') }}
            @endif
        </x-ui.empty-state>
    @else
        <ul class="ui-item-list" role="list">
            @foreach ($rows as $row)
                @php $item = $row['item']; @endphp
                <li class="ui-item-list__item">
                    <div class="ui-item-list__main">
                        <h2 class="ui-item-list__title"><a href="{{ route('dashboard.recognition.edit', $item) }}">{{ ($ar ? $item->title_ar : $item->title_en) ?? $item->title_ar ?? $item->title_en ?? __($R.'untitled') }}</a></h2>
                        <p class="ui-item-list__meta">
                            <x-ui.badge :variant="$badges[$row['state']][0]" :icon="$badges[$row['state']][1]">{{ __($R.'states.'.$row['state']) }}</x-ui.badge>
                            @if ($row['period'] !== null)
                                <span>{{ $row['period'] }}</span>
                            @endif
                            @if ($row['member'] !== null)
                                <span>{{ $row['member'] }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="ui-item-list__actions">
                        <x-ui.button size="sm" :href="route('dashboard.recognition.edit', $item)" icon="file-text">{{ __('dashboard.edit') }}</x-ui.button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
