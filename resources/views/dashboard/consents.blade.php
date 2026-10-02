@extends('layouts.dashboard')

@section('title', __('dashboard.consents.title'))

@section('content')
    {{--
        Settings → Consent texts: per form, the text people accept now, a new version (never an edit in place — each
        application keeps the version it accepted) with a required reason, and the earlier versions.
    --}}
    @php $C = 'dashboard.consents.'; @endphp
    <x-ui.page-header :title="__($C.'title')" :description="__($C.'description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.settings')">{{ __('dashboard.settings.title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="ui-menu-item">
        @foreach ($scopes as $scope => $s)
            @php $bag = $errors->getBag('consent-'.$scope); @endphp
            <section class="ui-record__section" id="consent-{{ $scope }}" aria-labelledby="consent-title-{{ $scope }}">
                <h2 id="consent-title-{{ $scope }}" class="ui-record__title">{{ __($C.'scopes.'.$scope) }}</h2>
                <p class="ui-record__status">
                    <span>{{ __($C.'active') }}:</span>
                    @if ($s['active'] !== null)
                        <x-ui.badge variant="success" icon="circle-check"><bdi>{{ $s['active']->version }}</bdi></x-ui.badge>
                        <span><bdi>{{ $s['active']->active_from->setTimezone('Asia/Amman')->format('Y-m-d') }}</bdi></span>
                    @else
                        <x-ui.badge variant="warning" icon="triangle-alert">{{ __($C.'none') }}</x-ui.badge>
                    @endif
                </p>
                @if ($bag->any())
                    <x-ui.error-summary :errors="collect($bag->getMessages())->mapWithKeys(fn ($m, $k) => [$k.'-'.$scope => $m])->all()" :title="__('dashboard.pages.errors.summary')" :id="'consent-errors-'.$scope" />
                @endif
                <form class="ui-record__form" method="post" action="{{ route('dashboard.consents.publish', $scope) }}">
                    @csrf
                    <div class="ui-bilingual">
                        @foreach ($s['languages'] as $locale)
                            <div class="ui-bilingual__column">
                                <x-ui.field :label="__('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english'))" :for="'text_'.$locale.'-'.$scope" :error="$bag->first('text_'.$locale)">
                                    <x-ui.textarea :id="'text_'.$locale.'-'.$scope" :name="'text_'.$locale" rows="8" maxlength="3000" :lang="$locale" :dir="$locale === 'ar' ? 'rtl' : 'ltr'"
                                        :value="$bag->any() ? old('text_'.$locale) : $s['active']?->{'text_'.$locale}" />
                                </x-ui.field>
                            </div>
                        @endforeach
                    </div>
                    <x-ui.field :label="__($C.'reason')" :for="'reason-'.$scope" :hint="__($C.'reason_hint')" :error="$bag->first('reason')">
                        <x-ui.input :id="'reason-'.$scope" name="reason" maxlength="300" :value="$bag->any() ? old('reason') : null" />
                    </x-ui.field>
                    <p class="ui-note">{{ __($C.'legal_note') }}</p>
                    <x-ui.button type="submit">{{ __($C.'publish') }}</x-ui.button>
                </form>
                @if ($s['history']->count() > 1)
                    <h3 class="ui-record__subtitle">{{ __($C.'history') }}</h3>
                    <ul class="ui-record__lines ui-menu-item__lines" role="list">
                        @foreach ($s['history'] as $v)
                            <li><bdi>{{ $v->version }}</bdi> · <bdi>{{ $v->active_from->setTimezone('Asia/Amman')->format('Y-m-d H:i') }}</bdi>@if ($v->is_active) · {{ __($C.'active') }}@endif</li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endforeach
    </div>
@endsection
