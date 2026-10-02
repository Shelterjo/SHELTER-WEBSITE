@extends('layouts.site')

@section('title', $event->title.' — '.__('site.brand'))

@section('content')
    {{-- SI-M08: the approved event text in the page language; dates in the event's time zone; no media until approved. --}}
    <div class="ui-page">
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                <p class="ui-eyebrow"><x-ui.badge>{{ __('site.events.state.'.$event->state) }}</x-ui.badge></p>
                <h1 class="ui-page-intro__title">{{ $event->title }}</h1>
            </header>
            @if ($event->image !== null)
                <x-ui.picture :image="$event->image" ratio="landscape" sizes="(min-width: 1024px) 60vw, 100vw" eager />
            @endif
            <div class="ui-event-detail">
                <dl class="ui-event-detail__facts">
                    <div class="ui-event-detail__fact">
                        <dt><x-ui.icon name="calendar" size="sm" /><span>{{ __('site.events.when') }}</span></dt>
                        <dd>
                            <time datetime="{{ $event->startsAt->toIso8601String() }}">{{ $event->dateText }}</time>
                            @if ($event->timeText !== null)
                                <br>{{ $event->timeText }}
                            @else
                                <span class="ui-event-detail__span">{{ __('site.events.starts', ['when' => $event->startText]) }}</span>
                                <span class="ui-event-detail__span">{{ __('site.events.ends', ['when' => $event->endText]) }}</span>
                            @endif
                        </dd>
                    </div>
                    @if ($event->place !== null)
                        <div class="ui-event-detail__fact">
                            <dt><x-ui.icon name="map-pin" size="sm" /><span>{{ __('site.events.where') }}</span></dt>
                            <dd>{{ $event->place }}</dd>
                        </div>
                    @endif
                </dl>
                @if ($event->isEnded())
                    <x-ui.alert>{{ __('site.events.ended_notice') }}</x-ui.alert>
                @endif
                @if (count($event->paragraphs) > 0)
                    <div class="ui-prose">
                        <section class="ui-prose__section">
                            @foreach ($event->paragraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </section>
                    </div>
                @endif
                @if ($event->ctaLabel !== null && ! $event->isEnded())
                    <p class="ui-event-detail__cta">
                        <x-ui.button size="lg" :href="$event->ctaUrl" icon-end="arrow-right" :rel="str_starts_with((string) $event->ctaUrl, 'https://') ? 'noopener' : null">{{ $event->ctaLabel }}</x-ui.button>
                    </p>
                @endif
                @if (count($event->terms) > 0)
                    <x-ui.disclosure :summary="__('site.events.terms')" class="ui-event-detail__terms">
                        @foreach ($event->terms as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </x-ui.disclosure>
                @endif
                <p><a class="ui-event-detail__back" href="{{ $listing }}"><x-ui.icon name="chevron-left" size="sm" /><span>{{ __('site.events.back') }}</span></a></p>
            </div>
        </div>
    </div>
@endsection
