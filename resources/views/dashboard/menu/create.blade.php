@extends('layouts.dashboard')

@section('title', __('dashboard.menu.create.title'))

@section('content')
    {{--
        A new menu item (MENU-060): its section, its names (the Owner's own Arabic name shows as typed), the first price
        and the day it starts, shown or hidden. Description, image and branch values are completed on its page after.
    --}}
    @php
        $M = 'dashboard.menu.';
        $bag = $errors->getBag('create');
    @endphp
    <x-ui.page-header :title="__($M.'create.title')" :description="__($M.'create.description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.menu.index')">{{ __($M.'back_to_menu') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($bag->any())
        <x-ui.error-summary :errors="collect($bag->getMessages())->mapWithKeys(fn ($m, $k) => [$k === 'reason' ? 'create-reason' : $k => $m])->all()" :title="__('dashboard.pages.errors.summary')" id="create-errors" />
    @endif
    <section class="ui-record__section" aria-labelledby="create-title">
        <h2 id="create-title" class="ui-record__title">{{ __($M.'groups.details') }}</h2>
        <form class="ui-record__form" method="post" action="{{ route('dashboard.menu.store') }}">
            @csrf
            <x-ui.field :label="__($M.'create.category')" for="category" :error="$bag->first('category')">
                <x-ui.select id="category" name="category" :options="$categories" :selected="(string) ($bag->any() ? old('category') : $selected)" placeholder />
            </x-ui.field>
            <x-ui.field :label="__($M.'fields.name_en')" for="name_en" :error="$bag->first('name_en')">
                <x-ui.input id="name_en" name="name_en" lang="en" dir="ltr" maxlength="120" :value="old('name_en')" />
            </x-ui.field>
            <x-ui.field :label="__($M.'fields.name_ar')" for="name_ar" :hint="__($M.'create.name_ar_hint')" :error="$bag->first('name_ar')" optional>
                <x-ui.input id="name_ar" name="name_ar" lang="ar" dir="rtl" maxlength="120" :value="old('name_ar')" />
            </x-ui.field>
            <div class="ui-editor__pair">
                <x-ui.field :label="__($M.'fields.price')" for="price" :hint="__($M.'fields.price_hint')" :error="$bag->first('price')">
                    <x-ui.input id="price" name="price" inputmode="decimal" dir="ltr" maxlength="7" autocomplete="off" :value="old('price')" />
                </x-ui.field>
                <x-ui.field :label="__($M.'fields.starts_on')" for="starts_on" :error="$bag->first('starts_on')">
                    <x-ui.input type="date" id="starts_on" name="starts_on" :value="old('starts_on', now('Asia/Amman')->toDateString())" />
                </x-ui.field>
            </div>
            <input type="hidden" name="visible" value="0">
            <x-ui.checkbox :label="__($M.'create.visible')" name="visible" value="1" id="visible" :checked="$bag->any() ? old('visible') === '1' : true" />
            <x-ui.field :label="__($M.'create.reason')" for="create-reason" :hint="__($M.'season.reason_hint')" optional>
                <x-ui.input id="create-reason" name="reason" maxlength="300" :value="old('reason')" />
            </x-ui.field>
            <x-ui.button type="submit">{{ __($M.'create.submit') }}</x-ui.button>
        </form>
    </section>
@endsection
