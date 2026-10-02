@extends('layouts.dashboard')

@section('title', __('dashboard.requests.settings.title'))

@section('content')
    {{--
        Careers settings (CAREERS-071/075): when an open application counts as waiting too long (a nudge in the list —
        never a deletion), the interview places and the cities of the form. Switching one off keeps older applications.
    --}}
    @php
        $S = 'dashboard.requests.settings.';
        $ar = app()->getLocale() === 'ar';
        $staleBag = $errors->getBag('stale');
        $newPlace = $errors->getBag('location-new');
        $newCity = $errors->getBag('city-new');
    @endphp
    <x-ui.page-header :title="__($S.'title')" :description="__($S.'description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.careers.index')">{{ __('dashboard.requests.careers_title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="ui-menu-item">
        <section class="ui-record__section" id="stale" aria-labelledby="stale-title">
            <h2 id="stale-title" class="ui-record__title">{{ __($S.'stale_title') }}</h2>
            <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.settings.stale') }}">
                @csrf
                @method('PUT')
                <x-ui.field :label="__($S.'stale_days')" for="stale_days" :hint="__($S.'stale_hint')" :error="$staleBag->first('stale_days')">
                    <x-ui.input type="number" id="stale_days" name="stale_days" inputmode="numeric" min="1" max="365" :value="$staleBag->any() ? old('stale_days') : $staleDays" />
                </x-ui.field>
                <x-ui.button type="submit" variant="secondary">{{ __('dashboard.save') }}</x-ui.button>
            </form>
        </section>

        <section class="ui-record__section" id="locations" aria-labelledby="locations-title">
            <h2 id="locations-title" class="ui-record__title">{{ __($S.'locations_title') }}</h2>
            <ul class="ui-record__notes" role="list">
                @foreach ($locations as $place)
                    @php $bag = $errors->getBag('location-'.$place->id); @endphp
                    <li class="ui-record__note">
                        <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.settings.locations.update', $place) }}">
                            @csrf
                            @method('PUT')
                            <div class="ui-editor__pair">
                                <x-ui.field :label="__($S.'name_ar')" :for="'place-ar-'.$place->id" :error="$bag->first('name_ar')">
                                    <x-ui.input :id="'place-ar-'.$place->id" name="name_ar" lang="ar" dir="rtl" maxlength="80" :value="$place->name_ar" />
                                </x-ui.field>
                                <x-ui.field :label="__($S.'name_en')" :for="'place-en-'.$place->id" :error="$bag->first('name_en')">
                                    <x-ui.input :id="'place-en-'.$place->id" name="name_en" lang="en" dir="ltr" maxlength="80" :value="$place->name_en" />
                                </x-ui.field>
                            </div>
                            <x-ui.checkbox :label="__($S.'active')" name="is_active" value="1" :id="'place-active-'.$place->id" :checked="$place->is_active" />
                            <x-ui.button type="submit" size="sm" variant="secondary">{{ __('dashboard.save') }}</x-ui.button>
                        </form>
                    </li>
                @endforeach
            </ul>
            <x-ui.disclosure :summary="__($S.'add_location')" :open="$newPlace->any()">
                <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.settings.locations.store') }}">
                    @csrf
                    <div class="ui-editor__pair">
                        <x-ui.field :label="__($S.'name_ar')" for="place-new-ar" :error="$newPlace->first('name_ar')">
                            <x-ui.input id="place-new-ar" name="name_ar" lang="ar" dir="rtl" maxlength="80" :value="old('name_ar')" />
                        </x-ui.field>
                        <x-ui.field :label="__($S.'name_en')" for="place-new-en" :error="$newPlace->first('name_en')">
                            <x-ui.input id="place-new-en" name="name_en" lang="en" dir="ltr" maxlength="80" :value="old('name_en')" />
                        </x-ui.field>
                    </div>
                    <x-ui.button type="submit">{{ __($S.'add') }}</x-ui.button>
                </form>
            </x-ui.disclosure>
        </section>

        <section class="ui-record__section" id="cities" aria-labelledby="cities-title">
            <h2 id="cities-title" class="ui-record__title">{{ __($S.'cities_title') }}</h2>
            <p class="ui-note">{{ __($S.'cities_help') }}</p>
            <ul class="ui-city-list" role="list">
                @foreach ($cities as $city)
                    <li>
                        <form class="ui-city-list__item" method="post" action="{{ route('dashboard.careers.settings.cities.update', $city) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="is_active" value="{{ $city->is_active ? '' : '1' }}">
                            <span @class(['ui-city-list__name', 'ui-city-list__name--off' => ! $city->is_active])>{{ $ar ? $city->name_ar : ($city->name_en ?? $city->name_ar) }}</span>
                            <x-ui.button type="submit" size="sm" variant="ghost">{{ $city->is_active ? __($S.'switch_off') : __($S.'switch_on') }}<span class="ui-visually-hidden"> — {{ $city->name_ar }}</span></x-ui.button>
                        </form>
                    </li>
                @endforeach
            </ul>
            <x-ui.disclosure :summary="__($S.'add_city')" :open="$newCity->any()">
                <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.settings.cities.store') }}">
                    @csrf
                    <div class="ui-editor__pair">
                        <x-ui.field :label="__($S.'name_ar')" for="city-new-ar" :error="$newCity->first('city_name_ar')">
                            <x-ui.input id="city-new-ar" name="name_ar" lang="ar" dir="rtl" maxlength="80" />
                        </x-ui.field>
                        <x-ui.field :label="__($S.'name_en')" for="city-new-en" optional>
                            <x-ui.input id="city-new-en" name="name_en" lang="en" dir="ltr" maxlength="80" />
                        </x-ui.field>
                    </div>
                    <x-ui.button type="submit">{{ __($S.'add') }}</x-ui.button>
                </form>
            </x-ui.disclosure>
        </section>
    </div>
@endsection
