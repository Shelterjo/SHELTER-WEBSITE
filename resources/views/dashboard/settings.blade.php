@extends('layouts.dashboard')

@section('title', __('dashboard.settings.title'))

@section('content')
    {{--
        Settings (M50): each public form open or closed (with what the site shows now), Safe Mode, anonymous search
        counting and the founding year. One form, one save; only changes are written, each audited.
    --}}
    @php
        $S = 'dashboard.settings.';
        $bag = $errors->getBag('settings');
        $v = fn (string $field, string $value): string => $bag->any() ? (string) old($field) : $value;
    @endphp
    <x-ui.page-header :title="__($S.'title')" :description="__($S.'description')">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('dashboard.consents')">{{ __($S.'consents_link') }}</x-ui.button>
            <x-ui.button variant="ghost" :href="route('dashboard.texts.index')">{{ __('dashboard.texts.title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>
    @if ($bag->any())
        <x-ui.error-summary :errors="$bag" :title="__('dashboard.pages.errors.summary')" id="settings-errors" />
    @endif

    <form class="ui-menu-item" method="post" action="{{ route('dashboard.settings.update') }}">
        @csrf
        @method('PUT')
        <section class="ui-record__section" aria-labelledby="forms-title">
            <h2 id="forms-title" class="ui-record__title">{{ __($S.'forms_title') }}</h2>
            <p class="ui-note">{{ __($S.'forms_help') }}</p>
            @foreach ($state['forms'] as $form => $f)
                @php $choice = $v('form_'.$form, $f['open'] ? 'open' : 'closed'); @endphp
                <x-ui.fieldset :legend="__($S.'forms.'.$form)" :id="'form-'.$form">
                    <p class="ui-record__status" data-form-state="{{ $form }}">
                        <span>{{ __($S.'now') }}:</span>
                        <x-ui.badge :variant="$f['live'] ? 'success' : 'neutral'" :icon="$f['live'] ? 'circle-check' : 'pause'">{{ __($S.($f['live'] ? 'open_now' : 'closed_now')) }}</x-ui.badge>
                        @if ($f['open'] && ! $f['live'])
                            <span>{{ __($S.($production ? 'gate' : 'not_ready')) }}</span>
                        @endif
                    </p>
                    <div class="ui-editor__options">
                        <x-ui.radio :label="__($S.'open')" :name="'form_'.$form" value="open" :id="'form-'.$form.'-open'" :checked="$choice === 'open'" />
                        <x-ui.radio :label="__($S.'closed')" :name="'form_'.$form" value="closed" :id="'form-'.$form.'-closed'" :checked="$choice === 'closed'" />
                    </div>
                </x-ui.fieldset>
            @endforeach
        </section>

        <section class="ui-record__section" aria-labelledby="safe-title">
            <h2 id="safe-title" class="ui-record__title">{{ __($S.'safe_title') }}</h2>
            <p class="ui-note">{{ __($S.'safe_help') }}</p>
            @php $safe = $v('safe_mode', $state['safe_mode'] ? 'on' : 'off'); @endphp
            <x-ui.fieldset :legend="__($S.'safe_title')" id="safe_mode">
                <div class="ui-editor__options">
                    <x-ui.radio :label="__($S.'safe_off')" name="safe_mode" value="off" id="safe-off" :checked="$safe === 'off'" />
                    <x-ui.radio :label="__($S.'safe_on')" name="safe_mode" value="on" id="safe-on" :checked="$safe === 'on'" />
                </div>
            </x-ui.fieldset>
        </section>

        <section class="ui-record__section" aria-labelledby="search-title">
            <h2 id="search-title" class="ui-record__title">{{ __($S.'search_title') }}</h2>
            <p class="ui-note">{{ __($S.'search_help') }}</p>
            @php $log = $v('search_log', $state['search_log'] ? 'on' : 'off'); @endphp
            <x-ui.fieldset :legend="__($S.'search_title')" id="search_log">
                <div class="ui-editor__options">
                    <x-ui.radio :label="__($S.'search_off')" name="search_log" value="off" id="search-off" :checked="$log === 'off'" />
                    <x-ui.radio :label="__($S.'search_on')" name="search_log" value="on" id="search-on" :checked="$log === 'on'" />
                </div>
            </x-ui.fieldset>
        </section>

        <section class="ui-record__section" aria-labelledby="brand-title">
            <h2 id="brand-title" class="ui-record__title">{{ __($S.'brand_title') }}</h2>
            <x-ui.field :label="__($S.'founded_year')" for="founded_year" :hint="__($S.'founded_hint')" :error="$bag->first('founded_year')" optional>
                <x-ui.input id="founded_year" name="founded_year" inputmode="numeric" dir="ltr" maxlength="4" :value="$v('founded_year', (string) ($state['founded_year'] ?? ''))" />
            </x-ui.field>
            <x-ui.field :label="__($S.'reason')" for="reason" :hint="__($S.'reason_hint')" optional>
                <x-ui.input id="reason" name="reason" maxlength="300" />
            </x-ui.field>
            <x-ui.button type="submit">{{ __($S.'save') }}</x-ui.button>
        </section>
    </form>
@endsection
