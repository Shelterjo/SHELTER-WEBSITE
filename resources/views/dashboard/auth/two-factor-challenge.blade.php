@extends('layouts.auth')

@section('title', __('dashboard.auth.two_factor_title'))

@section('content')
    <p>{{ __('dashboard.auth.two_factor_help') }}</p>
    @include('dashboard.auth._errors')
    <form method="post" action="{{ route('two-factor.verify') }}" class="ui-stack" novalidate>
        @csrf
        <div class="ui-field">
            <label class="ui-label" for="code">{{ __('dashboard.auth.code') }}</label>
            <input class="ui-input" id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                   dir="ltr" maxlength="12" @error('code') aria-invalid="true" aria-describedby="form-errors" @enderror>
        </div>
        <details class="ui-details">
            <summary>{{ __('dashboard.auth.use_recovery') }}</summary>
            <div class="ui-field">
                <label class="ui-label" for="recovery_code">{{ __('dashboard.auth.recovery_code') }}</label>
                <input class="ui-input" id="recovery_code" name="recovery_code" type="text" dir="ltr" autocomplete="off" maxlength="20">
            </div>
        </details>
        <button type="submit" class="ui-btn ui-btn--primary">{{ __('dashboard.auth.verify') }}</button>
    </form>
@endsection
