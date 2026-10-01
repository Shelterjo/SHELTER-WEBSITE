@extends('layouts.auth')

@section('title', __('dashboard.auth.setup_title'))

@section('content')
    <ol class="ui-stack">
        <li>{{ __('dashboard.auth.setup_step1') }}</li>
        <li>{{ __('dashboard.auth.setup_step2') }}</li>
    </ol>
    <figure class="ui-qr">
        {!! $qr !!}{{-- nosemgrep: shelter-blade-unescaped-output — SVG generated server-side by bacon-qr-code from the TOTP URI --}}
        <figcaption>{{ __('dashboard.auth.setup_manual') }} <code dir="ltr">{{ $secret }}</code></figcaption>
    </figure>
    @include('dashboard.auth._errors')
    <form method="post" action="{{ route('two-factor.enable') }}" class="ui-stack" novalidate>
        @csrf
        <div class="ui-field">
            <label class="ui-label" for="code">{{ __('dashboard.auth.code') }}</label>
            <input class="ui-input" id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" dir="ltr" maxlength="12" required>
        </div>
        <button type="submit" class="ui-btn ui-btn--primary">{{ __('dashboard.auth.enable') }}</button>
    </form>
@endsection
