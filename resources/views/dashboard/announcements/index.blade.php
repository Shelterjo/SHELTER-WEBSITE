@extends('layouts.dashboard')

@section('title', __('dashboard.announcements.title'))

@section('content')
    {{-- Content → Announcements and campaigns: each one with its state for visitors in plain words; one click to edit. --}}
    @php
        $A = 'dashboard.announcements.';
        $badges = [
            'live' => ['success', 'circle-check'], 'outranked' => ['warning', 'triangle-alert'], 'upcoming' => ['info', 'calendar'],
            'draft' => ['neutral', 'file-text'], 'paused' => ['warning', 'pause'], 'incomplete' => ['warning', 'triangle-alert'],
            'ended' => ['neutral', 'clock'], 'cancelled' => ['neutral', 'ban'], 'archived' => ['neutral', 'inbox'],
        ];
        $ar = app()->getLocale() === 'ar';
    @endphp
    <x-ui.page-header :title="__($A.'title')" :description="__($A.'description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.live')" icon="clock">{{ __('dashboard.nav.live') }}</x-ui.button>
            <x-ui.button :href="route('dashboard.announcements.create')" icon="message-square-text">{{ __($A.'add') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <nav class="ui-filter-bar" aria-label="{{ __($A.'title') }}">
        @foreach (['current', 'past', 'archived'] as $key)
            <x-ui.chip :href="route('dashboard.announcements.index', $key === 'current' ? [] : ['show' => $key])" :current="$tab === $key">{{ __('dashboard.events.tabs.'.$key) }} <bdi>({{ $counts[$key] }})</bdi></x-ui.chip>
        @endforeach
    </nav>

    @if ($rows === [])
        <x-ui.empty-state :title="__($A.'empty')" icon="message-square-text">
            @if ($tab === 'current')
                {{ __($A.'empty_hint') }}
            @endif
        </x-ui.empty-state>
    @else
        <ul class="ui-item-list" role="list">
            @foreach ($rows as $row)
                @php
                    $item = $row['item'];
                    // CAMP-004: the Owner's own name leads the row; the visitors' title follows it.
                    $internal = $item->details['internal_name'] ?? null;
                    $public = ($ar ? $item->title_ar : $item->title_en) ?? $item->title_ar ?? $item->title_en;
                    $only = array_values(array_filter(array_map(fn ($id) => $branches[(int) $id] ?? null, $item->branch_ids ?? [])));
                @endphp
                <li class="ui-item-list__item">
                    <div class="ui-item-list__main">
                        <h2 class="ui-item-list__title"><a href="{{ route('dashboard.announcements.edit', $item) }}">{{ $internal ?? $public ?? __($A.'untitled') }}</a></h2>
                        <p class="ui-item-list__meta">
                            <x-ui.badge :variant="$badges[$row['state']][0]" :icon="$badges[$row['state']][1]">{{ __($A.'states.'.$row['state']) }}</x-ui.badge>
                            @if ($internal !== null && $public !== null)
                                <span>{{ $public }}</span>
                            @endif
                            <span>{{ __($A.'types.'.(($item->details['urgent'] ?? false) === true ? 'urgent' : $item->type)) }}</span>
                            @if (($item->placements ?? []) !== [])
                                <span>{{ __($A.'placements.'.$item->placements[0]) }}</span>
                            @endif
                            @if ($only !== [])
                                <span>{{ __($A.'only_branches', ['branches' => implode('، ', $only)]) }}</span>
                            @endif
                            @if ($row['when'] !== null)
                                <span><bdi>{{ $row['when'] }} → {{ $row['until'] }}</bdi></span>
                            @endif
                        </p>
                    </div>
                    <div class="ui-item-list__actions">
                        <x-ui.button size="sm" :href="route('dashboard.announcements.edit', $item)" icon="file-text">{{ __('dashboard.edit') }}</x-ui.button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
