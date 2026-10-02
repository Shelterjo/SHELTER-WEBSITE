@extends('layouts.dashboard')

@section('title', __('dashboard.media.title'))

@section('content')
    {{-- Content → Images: the library as a grid, filtered by the Owner's decision; one click opens an image. --}}
    @php
        $states = [\App\Models\Media::PENDING => ['warning', 'clock'], \App\Models\Media::APPROVED => ['success', 'circle-check'], \App\Models\Media::REJECTED => ['danger', 'circle-x']];
    @endphp
    <x-ui.page-header :title="__('dashboard.media.title')" :description="__('dashboard.media.description')">
        <x-slot:actions>
            <x-ui.button :href="route('dashboard.media.create')" icon="upload">{{ __('dashboard.media.upload') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <nav class="ui-filter-bar" aria-label="{{ __('dashboard.media.filters_label') }}">
        @foreach ($counts as $name => $count)
            <x-ui.chip :href="route('dashboard.media.index', ['show' => $name])" :current="$filter === $name">{{ __('dashboard.media.filters.'.$name) }} <bdi>({{ $count }})</bdi></x-ui.chip>
        @endforeach
    </nav>

    @if ($items === [])
        <x-ui.empty-state :title="__('dashboard.media.empty')" icon="image">
            <x-slot:actions>
                <x-ui.button :href="route('dashboard.media.create')" icon="upload">{{ __('dashboard.media.upload') }}</x-ui.button>
            </x-slot:actions>
        </x-ui.empty-state>
    @else
        <ul class="ui-media-grid" role="list">
            @foreach ($items as $item)
                @php
                    $m = $item['media'];
                    $w = min(480, $m->width ?? 480);
                    $h = $m->width ? (int) round(($m->height ?? $m->width) * $w / $m->width) : $w;
                    $state = $m->archived_at !== null ? ['neutral', 'inbox'] : ($states[$m->approval_status] ?? ['neutral', 'clock']);
                @endphp
                <li class="ui-media-tile">
                    <a class="ui-media-tile__link" href="{{ route('dashboard.media.edit', $m) }}">
                        <img class="ui-media-tile__image" src="{{ route('dashboard.media.preview', $m) }}" alt="" width="{{ $w }}" height="{{ $h }}" loading="lazy" decoding="async">
                        <span class="ui-media-tile__code"><bdi>{{ $m->code }}</bdi></span>
                    </a>
                    <p class="ui-media-tile__meta">
                        <x-ui.badge :variant="$state[0]" :icon="$state[1]">{{ __('dashboard.media.states.'.($m->archived_at !== null ? 'archived' : $m->approval_status)) }}</x-ui.badge>
                        @if ($m->approval_status === \App\Models\Media::APPROVED && $m->archived_at === null)
                            <span>{{ $item['live'] ? __('dashboard.media.live') : __('dashboard.media.not_live') }}</span>
                        @endif
                    </p>
                </li>
            @endforeach
        </ul>
        <x-ui.pagination :current="$current" :total="$last" :url="fn (int $page) => route('dashboard.media.index', ['show' => $filter, 'page' => $page])" />
    @endif
@endsection
