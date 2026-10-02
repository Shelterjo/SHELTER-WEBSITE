@extends('layouts.site')
@php($description = filled($description ?? null) ? $description : __('site.meta.branch', ['name' => $branch->name]))

@section('title', __('site.branch.title', ['name' => $branch->name, 'kind' => $branch->titleKind ?? $branch->kind ?? '']))

@section('content')
    {{-- The Owner's preview of details not published yet (BRANCH-010, dashboard only, noindex): said so, and never measured. --}}
    <div class="ui-page ui-page--with-bar" @unless (isset($preview)) data-track-view="branch_view" data-track-branch="{{ $branch->branch->slug }}" data-track-placement="branch_page" @endunless>
        <div class="ui-container">
            @isset($preview)
                <x-ui.alert variant="warning" :title="__('dashboard.branch.preview_page_title')">{{ __($preview['hidden'] ? 'dashboard.branch.preview_page_hidden' : 'dashboard.branch.preview_page_help') }}</x-ui.alert>
            @endisset
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                @if ($branch->altName !== null)
                    <p class="ui-eyebrow"><span lang="{{ $branch->altLocale }}" dir="{{ $branch->altLocale === 'ar' ? 'rtl' : 'ltr' }}">{{ $branch->altName }}</span></p>
                @endif
                <h1 class="ui-page-intro__title">{{ $branch->name }}</h1>
                @if ($branch->kindInCity() !== null)
                    <p class="ui-page-intro__lead">{{ $branch->kindInCity() }}</p>
                @endif
                @if ($branch->status !== null)
                    <x-ui.open-status :timeline="$branch->status" size="lg" />
                    @if ($branch->status->exception !== null)
                        <p class="ui-note">{{ __('site.status.special') }}</p>
                    @endif
                @endif
            </header>

            <div class="ui-split">
                @if ($branch->week !== null)
                    <section class="ui-split__main" aria-labelledby="branch-hours" data-ui-reveal>
                        <h2 class="ui-split__title" id="branch-hours">{{ __('site.hours.title') }}</h2>
                        <x-ui.hours-table :rows="$branch->week" :caption="__('site.hours.caption')" />
                        <p class="ui-note">{{ __('site.hours.timezone', ['market' => $market->name()]) }}</p>
                    </section>
                @endif
                @if ($branch->landmark !== null || $branch->address !== null || $branch->mapsUrl !== null || $branch->services !== [] || $branch->payments !== [])
                    <section class="ui-split__aside" aria-labelledby="branch-place" data-ui-reveal>
                        <h2 class="ui-split__title" id="branch-place">{{ __('site.branch.place') }}</h2>
                        @if ($branch->landmark !== null)
                            <p>{{ $branch->landmark }}</p>
                        @endif
                        @if ($branch->address !== null)
                            <p>{{ $branch->address }}</p>
                        @endif
                        @if ($branch->mapsUrl !== null)
                            <x-ui.button variant="secondary" icon="map-pin" :href="$branch->mapsUrl" rel="noopener" target="_blank">{{ __('site.branch.directions') }}</x-ui.button>
                        @endif
                        @foreach (['services' => 'service', 'payments' => 'payment'] as $list => $group)
                            @if ($branch->{$list} !== [])
                                <h3 class="ui-split__title">{{ __('site.branch.'.$list) }}</h3>
                                <ul class="ui-cluster" role="list">
                                    @foreach ($branch->{$list} as $key)
                                        <li><x-ui.badge>{{ __('site.attributes.'.$group.'.'.$key) }}</x-ui.badge></li>
                                    @endforeach
                                </ul>
                            @endif
                        @endforeach
                    </section>
                @endif
                @if ($branch->phone !== null || $branch->whatsapp !== null || $menuUrl !== null)
                    <aside class="ui-split__aside" aria-labelledby="branch-contact" data-ui-reveal>
                        <h2 class="ui-split__title" id="branch-contact">{{ __('site.branch.contact') }}</h2>
                        @if ($branch->phone !== null)
                            <p class="ui-contact-number"><a href="{{ $branch->phone->href }}" dir="ltr">{{ $branch->phone->display }}</a></p>
                        @endif
                        <div class="ui-contact-actions">
                            @if ($branch->phone !== null)
                                <x-ui.button variant="secondary" icon="phone" :href="$branch->phone->href">{{ __('site.branch.call') }}</x-ui.button>
                            @endif
                            @if ($branch->whatsapp !== null)
                                <x-ui.button variant="secondary" icon="message-circle" :href="$branch->whatsapp->href" rel="noopener" target="_blank">{{ __('site.branch.whatsapp') }}</x-ui.button>
                            @endif
                            @if ($menuUrl !== null)
                                <x-ui.button variant="link" :href="$menuUrl" icon-end="arrow-right">{{ __('site.branch.menu') }}</x-ui.button>
                            @endif
                        </div>
                    </aside>
                @endif
            </div>
        </div>

        @if ($branch->phone !== null || $branch->whatsapp !== null || $branch->mapsUrl !== null)
            {{-- D-061: [Directions] [Call] [WhatsApp] — Directions shows once the Owner saves the Maps link (PO-010). --}}
            <x-ui.action-bar :label="__('site.branch.actions')" data-track-placement="action_bar">
                @if ($branch->mapsUrl !== null)
                    <x-ui.button variant="secondary" icon="map-pin" :href="$branch->mapsUrl" rel="noopener" target="_blank">{{ __('site.branch.directions_short') }}</x-ui.button>
                @endif
                @if ($branch->phone !== null)
                    <x-ui.button icon="phone" :href="$branch->phone->href">{{ __('site.branch.call') }}</x-ui.button>
                @endif
                @if ($branch->whatsapp !== null)
                    <x-ui.button variant="secondary" icon="message-circle" :href="$branch->whatsapp->href" rel="noopener" target="_blank">{{ __('site.branch.whatsapp_short') }}</x-ui.button>
                @endif
            </x-ui.action-bar>
        @endif
    </div>
@endsection
