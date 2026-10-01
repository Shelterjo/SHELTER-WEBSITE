@extends('layouts.auth')

@section('title', __('dashboard.auth.two_factor_title'))

@section('content')
    <p>{{ __('dashboard.auth.two_factor_help') }}</p>
    @include('dashboard.auth._errors')
    <form method="post" action="{{ route('two-factor.verify') }}" class="ui-stack" novalidate>
        @csrf
        <x-ui.field :label="__('dashboard.auth.code')" for="code" :error="$errors->first('code')">
            <x-ui.input name="code" inputmode="numeric" autocomplete="one-time-code" dir="ltr" maxlength="12" />
        </x-ui.field>
        <x-ui.disclosure :summary="__('dashboard.auth.use_recovery')" :open="$errors->has('recovery_code')">
            <x-ui.field :label="__('dashboard.auth.recovery_code')" for="recovery_code" :error="$errors->first('recovery_code')">
                <x-ui.input name="recovery_code" dir="ltr" autocomplete="off" maxlength="20" />
            </x-ui.field>
        </x-ui.disclosure>
        <x-ui.button type="submit">{{ __('dashboard.auth.verify') }}</x-ui.button>
    </form>
@endsection
