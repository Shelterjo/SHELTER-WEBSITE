@extends('layouts.site')

@section('title', __('site.events.title').' — '.__('site.brand'))

@section('content')
    {{-- SI-M07: what is on now, then what is coming. Empty → a calm empty state and noindex (never an empty grid). --}}
    <div class="ui-page">
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title">{{ __('site.events.title') }}</h1>
                <p class="ui-page-intro__lead">{{ __('site.events.lead') }}</p>
            </header>
            @if (count($events) > 0)
                <ul class="ui-panel-grid ui-events-grid" role="list">
                    @foreach ($events as $event)
                        <li data-ui-reveal>
                            <x-ui.event-card :title="$event->title" :href="$event->url" :level="2"
                                :date="$event->timeText !== null ? $event->dateText.' · '.$event->timeText : $event->dateText" :datetime="$event->startsAt->toIso8601String()"
                                :place="$event->place" :badge="__('site.events.state.'.$event->state)">
                                @if (count($event->paragraphs) > 0)
                                    <p>{{ $event->paragraphs[0] }}</p>
                                @endif
                            </x-ui.event-card>
                        </li>
                    @endforeach
                </ul>
                <p class="ui-note">{{ __('site.hours.timezone', ['market' => $market->name()]) }}</p>
            @else
                <x-ui.empty-state icon="calendar" :title="__('site.events.empty_title')">{{ __('site.events.empty_text') }}</x-ui.empty-state>
            @endif
        </div>
    </div>
@endsection
