@extends('layouts.auth')

@section('title', __('dashboard.auth.setup_title'))

@section('content')
    <ol class="ui-auth__steps">
        <li>{{ __('dashboard.auth.setup_step1') }}</li>
        <li>{{ __('dashboard.auth.setup_step2') }}</li>
    </ol>
    <figure class="ui-auth__qr">
        {!! $qr !!}{{-- nosemgrep: shelter-blade-unescaped-output — SVG generated server-side by bacon-qr-code from the TOTP URI --}}
        <figcaption>{{ __('dashboard.auth.setup_manual') }} <code dir="ltr">{{ $secret }}</code></figcaption>
    </figure>
    @include('dashboard.auth._errors')
    <form method="post" action="{{ route('two-factor.enable') }}" class="ui-stack" novalidate>
        @csrf
        <x-ui.field :label="__('dashboard.auth.code')" for="code" :error="$errors->first('code')" required>
            <x-ui.input name="code" inputmode="numeric" autocomplete="one-time-code" dir="ltr" maxlength="12" />
        </x-ui.field>
        <x-ui.button type="submit">{{ __('dashboard.auth.enable') }}</x-ui.button>
    </form>
@endsection
