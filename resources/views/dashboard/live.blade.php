@extends('layouts.dashboard')

@section('title', __('dashboard.live.title'))

@section('content')
    {{--
        Active now (DX-020/021): per place, what shows and what waits behind it; live events; special hours and closures
        today; each with its end and "Stop now"; then what starts in the next 7 days. Same resolver as the site.
    --}}
    @php
        $L = 'dashboard.live.';
        $ar = app()->getLocale() === 'ar';
        $name = fn ($e) => ($ar ? $e->title_ar : $e->title_en) ?? $e->title_ar ?? '#'.$e->id;
        $at = fn ($d) => $d === null ? '' : \Carbon\CarbonImmutable::instance($d)->setTimezone($timezone)->format('Y-m-d H:i');
        $edit = fn ($e) => $e->type === 'event' ? route('dashboard.events.edit', $e) : route('dashboard.announcements.edit', $e);
    @endphp
    <x-ui.page-header :title="__($L.'title')" :description="__($L.'description')" />

    <div class="ui-menu-item">
        @foreach ($places as $placement => $place)
            <section class="ui-record__section" aria-labelledby="place-{{ $placement }}">
                <h2 id="place-{{ $placement }}" class="ui-record__title">{{ __('dashboard.announcements.placements.'.$placement) }}</h2>
                @if ($place['winner'] === null)
                    <p class="ui-note">{{ __($L.'nothing') }}</p>
                @else
                    @php $w = $place['winner']; @endphp
                    <div class="ui-live-item">
                        <p class="ui-record__status">
                            <x-ui.badge variant="success" icon="circle-check">{{ __($L.'showing') }}</x-ui.badge>
                            <a href="{{ $edit($w) }}">{{ $name($w) }}</a>
                            <span>{{ __($L.'until', ['at' => $at($w->ends_at)]) }}</span>
                        </p>
                        <form method="post" action="{{ route('dashboard.live.disable', $w) }}">
                            @csrf
                            <x-ui.button type="submit" size="sm" variant="outline" icon="ban">{{ __($L.'stop') }}</x-ui.button>
                        </form>
                    </div>
                    @foreach ($place['waiting'] as $other)
                        <div class="ui-live-item">
                            <p class="ui-record__status">
                                <x-ui.badge variant="warning" icon="triangle-alert">{{ __($L.'waiting') }}</x-ui.badge>
                                <a href="{{ $edit($other) }}">{{ $name($other) }}</a>
                                <span>{{ __($L.'waiting_help') }}</span>
                            </p>
                            <form method="post" action="{{ route('dashboard.live.disable', $other) }}">
                                @csrf
                                <x-ui.button type="submit" size="sm" variant="ghost" icon="ban">{{ __($L.'stop') }}</x-ui.button>
                            </form>
                        </div>
                    @endforeach
                @endif
            </section>
        @endforeach

        <section class="ui-record__section" aria-labelledby="live-events">
            <h2 id="live-events" class="ui-record__title">{{ __('dashboard.nav.events') }}</h2>
            @forelse ($events as $event)
                <div class="ui-live-item">
                    <p class="ui-record__status">
                        <x-ui.badge variant="success" icon="calendar">{{ __($L.'on_now') }}</x-ui.badge>
                        <a href="{{ $edit($event) }}">{{ $name($event) }}</a>
                        <span>{{ __($L.'until', ['at' => $at($event->ends_at)]) }}</span>
                    </p>
                    <form method="post" action="{{ route('dashboard.live.disable', $event) }}">
                        @csrf
                        <x-ui.button type="submit" size="sm" variant="outline" icon="ban">{{ __($L.'stop') }}</x-ui.button>
                    </form>
                </div>
            @empty
                <p class="ui-note">{{ __($L.'no_events') }}</p>
            @endforelse
        </section>

        <section class="ui-record__section" aria-labelledby="live-hours">
            <h2 id="live-hours" class="ui-record__title">{{ __($L.'hours_title') }}</h2>
            @forelse ($exceptions as $exception)
                <p class="ui-record__status">
                    <x-ui.badge :variant="in_array($exception->kind->value, ['emergency', 'temporary'], true) ? 'warning' : 'info'" icon="store">{{ __('dashboard.hours.kinds.'.$exception->kind->value) }}</x-ui.badge>
                    <a href="{{ route('dashboard.branches.show', $exception->branch_id) }}">{{ $branchNames[$exception->branch_id] ?? '' }}</a>
                    <span>{{ __($L.'until', ['at' => $exception->ends_on->format('Y-m-d')]) }}</span>
                </p>
            @empty
                <p class="ui-note">{{ __($L.'no_hours') }}</p>
            @endforelse
        </section>

        <section class="ui-record__section" aria-labelledby="live-next">
            <h2 id="live-next" class="ui-record__title">{{ __($L.'next_title') }}</h2>
            @forelse ($upcoming as $next)
                <p class="ui-record__status">
                    <x-ui.badge variant="info" icon="calendar">{{ __('dashboard.announcements.types.'.(($next->details['urgent'] ?? false) === true ? 'urgent' : $next->type)) }}</x-ui.badge>
                    <a href="{{ $edit($next) }}">{{ $name($next) }}</a>
                    <span><bdi>{{ $at($next->starts_at) }}</bdi></span>
                </p>
            @empty
                <p class="ui-note">{{ __($L.'no_next') }}</p>
            @endforelse
        </section>
    </div>
@endsection
