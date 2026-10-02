@extends('layouts.site')
@php($description = filled($description ?? null) ? $description : __('site.meta.contact'))

@section('title', __('site.titles.contact'))

@section('content')
    {{-- SI-B04, D-059: one card per reason for getting in touch; intent numbers appear only here (never on cards). --}}
    <div class="ui-page">
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title">{{ __('site.contact.title') }}</h1>
                <p class="ui-page-intro__lead">{{ __('site.contact.lead') }}</p>
            </header>

            <ul class="ui-contact-grid" role="list" data-track-placement="contact">
                @if ($phone !== null || $whatsapp !== null || count($branches) > 0)
                    <li class="ui-contact-grid__wide" data-ui-reveal>
                        <x-ui.contact-card icon="store" :title="__('site.contact.general.title')" :lead="__('site.contact.general.lead')"
                            :phone="$phone" :whatsapp="$whatsapp" :email="$email" :href="$locationsUrl" :link-label="__('site.contact.general.link')" wide data-track-purpose="general">
                            @if (count($branches) > 0)
                                <ul class="ui-contact-card__list" role="list" aria-label="{{ __('site.contact.general.branches') }}">
                                    @foreach ($branches as $branch)
                                        @php($statusTimeline = $branch->status)
                                        <li class="ui-contact-card__item">
                                            <a class="ui-contact-card__item-link" href="{{ $branch->url }}">{{ $branch->name }}</a>
                                            @if ($statusTimeline !== null)
                                                <x-ui.open-status :timeline="$statusTimeline" />
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </x-ui.contact-card>
                    </li>
                @endif
                @if ($complaints !== null)
                    <li data-ui-reveal>
                        <x-ui.contact-card icon="message-square-text" :title="__('site.contact.complaints.title')" :lead="__('site.contact.complaints.lead')" :phone="$complaints" data-track-purpose="complaints_feedback_franchise" />
                    </li>
                @endif
                @if ($catering !== null)
                    <li data-ui-reveal>
                        <x-ui.contact-card icon="briefcase-business" :title="__('site.contact.catering.title')" :lead="__('site.contact.catering.lead')" :phone="$catering" data-track-purpose="catering_b2b_events" />
                    </li>
                @endif
                @if ($complaints !== null)
                    {{-- D-071: the franchise inquiries card is shown from launch; the franchise page links once published. --}}
                    <li data-ui-reveal>
                        <x-ui.contact-card icon="handshake" :title="__('site.contact.franchise.title')" :lead="__('site.contact.franchise.lead')"
                            :phone="$complaints" :href="$franchiseUrl" :link-label="__('site.contact.franchise.link')" data-track-purpose="complaints_feedback_franchise" />
                    </li>
                @endif
            </ul>
            @if (count($branches) > 0)
                <p class="ui-note">{{ __('site.contact.status_note') }}</p>
            @endif
        </div>
    </div>
@endsection
