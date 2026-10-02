@extends('layouts.dashboard')

@section('title', __('dashboard.branches.title'))

@section('content')
    {{-- Business data → Branches and hours: each branch, open or closed now, and its upcoming exceptions. --}}
    <x-ui.page-header :title="__('dashboard.branches.title')" :description="__('dashboard.branches.description')" />
    <ul class="ui-item-list" role="list">
        @foreach ($rows as $row)
            <li class="ui-item-list__item">
                <div class="ui-item-list__main">
                    <h2 class="ui-item-list__title"><a href="{{ route('dashboard.branches.show', $row['branch']) }}">{{ $row['name'] }}</a></h2>
                    @if (! $row['branch']->is_public)
                        <x-ui.badge icon="ban">{{ __('dashboard.branch.hidden_badge') }}</x-ui.badge>
                    @endif
                    <p class="ui-item-list__meta">
                        @if ($row['state'] === null)
                            <x-ui.badge icon="circle-alert">{{ __('dashboard.branches.unknown_now') }}</x-ui.badge>
                        @elseif ($row['state']->isOpen)
                            <x-ui.badge variant="success" icon="circle-check">{{ __('dashboard.branches.open_now') }}</x-ui.badge>
                        @else
                            <x-ui.badge icon="clock">{{ __('dashboard.branches.closed_now') }}</x-ui.badge>
                        @endif
                        <span>{{ trans_choice('dashboard.branches.exceptions_count', $row['exceptions'], ['count' => $row['exceptions']]) }}</span>
                    </p>
                </div>
                <div class="ui-item-list__actions">
                    <x-ui.button size="sm" :href="route('dashboard.branches.show', $row['branch'])" icon="clock">{{ __('dashboard.branches.manage') }}</x-ui.button>
                </div>
            </li>
        @endforeach
    </ul>
@endsection
