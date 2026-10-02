@extends('layouts.dashboard')

@section('title', __('dashboard.pages.title'))

@section('content')
    {{-- Content → Pages: every fixed brand page, its state on the site in plain words, and one click to edit. --}}
    @php
        $badges = ['live' => ['success', 'circle-check'], 'draft' => ['neutral', 'file-text'], 'missing' => ['neutral', 'circle-alert'], 'blocked' => ['warning', 'triangle-alert']];
    @endphp
    <x-ui.page-header :title="__('dashboard.pages.title')" :description="__('dashboard.pages.description')" />
    <ul class="ui-item-list" role="list">
        @foreach ($rows as $row)
            <li class="ui-item-list__item">
                <div class="ui-item-list__main">
                    <h2 class="ui-item-list__title"><a href="{{ route('dashboard.pages.edit', $row['key']) }}">{{ $row['label'] }}</a></h2>
                    <p class="ui-item-list__meta">
                        <x-ui.badge :variant="$badges[$row['state']][0]" :icon="$badges[$row['state']][1]">{{ __('dashboard.pages.states.'.$row['state']) }}</x-ui.badge>
                        @if ($row['sections'] > 0)
                            <span>{{ __('dashboard.pages.columns.sections') }}: <bdi>{{ $row['sections'] }}</bdi></span>
                        @endif
                        @if ($row['updated'] !== null)
                            <span>{{ __('dashboard.pages.columns.updated') }}: <time datetime="{{ $row['updated']->toIso8601String() }}"><bdi>{{ $row['updated']->timezone('Asia/Amman')->format('Y-m-d H:i') }}</bdi></time></span>
                        @endif
                    </p>
                    @if ($row['state'] === 'blocked')
                        <p class="ui-note">{{ __('dashboard.pages.blocked_help') }}</p>
                    @endif
                </div>
                <div class="ui-item-list__actions">
                    <x-ui.button size="sm" :href="route('dashboard.pages.edit', $row['key'])" icon="file-text">{{ __('dashboard.edit') }}</x-ui.button>
                    @if ($row['url'] !== null)
                        <x-ui.button size="sm" variant="ghost" :href="$row['url']" icon-end="arrow-right" target="_blank" rel="noopener">{{ __('dashboard.view_on_site') }}</x-ui.button>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
@endsection
