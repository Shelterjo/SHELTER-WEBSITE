@extends('layouts.dashboard')

@section('title', __('dashboard.history.versions_title', ['item' => $name]))

@section('content')
    {{--
        One item's saved versions (App\Services\Dashboard\VersionRestore): newest first, with who, when, the state and
        the reason. An earlier version that can come back has "Preview the restore"; restoring makes a new version and
        never removes one. Why a version cannot come back is said in words.
    --}}
    @php
        $H = 'dashboard.history.';
        $date = fn ($v) => $v?->setTimezone('Asia/Amman')->format('Y-m-d H:i');
    @endphp
    <x-ui.page-header :title="__($H.'versions_title', ['item' => $name])" :description="__($H.'versions_description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.history', ['item' => $type.':'.$id])">{{ __($H.'item_history') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($versions === [])
        <x-ui.empty-state :title="__($H.'no_versions')" />
    @else
        <ul class="ui-record__notes" role="list">
            @foreach ($versions as $version)
                @php $blocker = $blockers[$version->id] ?? null; @endphp
                <li class="ui-record__section ui-history__entry" id="v{{ $version->version }}">
                    <p class="ui-record__status">
                        <strong>{{ __($H.'version', ['version' => $version->version]) }}</strong>
                        @if ($version->version === $latest)
                            <x-ui.badge variant="success" icon="circle-check">{{ __($H.'latest') }}</x-ui.badge>
                        @endif
                        @if (\Illuminate\Support\Facades\Lang::has($H.'values.'.$version->status))
                            <x-ui.badge>{{ __($H.'values.'.$version->status) }}</x-ui.badge>
                        @endif
                    </p>
                    <p class="ui-note">{{ $version->user?->name ?? __($H.'actors.system') }} · <bdi dir="ltr">{{ $date($version->created_at) }}</bdi></p>
                    @if (filled($version->reason) && $version->reason !== 'dashboard')
                        <p class="ui-record__text">{{ __($H.'reason', ['reason' => $version->reason]) }}</p>
                    @endif
                    @if ($version->version !== $latest)
                        @if ($blocker === null)
                            <div class="ui-cluster">
                                <x-ui.button size="sm" variant="secondary" :href="route('dashboard.history.restore', $version)" icon="rotate-cw">{{ __($H.'preview_restore') }}</x-ui.button>
                            </div>
                        @else
                            <p class="ui-note">{{ __($H.'blocked.'.$blocker) }}</p>
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
        <x-ui.pagination :current="$current" :total="$last" :url="fn (int $page) => route('dashboard.history.versions', [$type, $id, 'page' => $page])" />
    @endif
@endsection
