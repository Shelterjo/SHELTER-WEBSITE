@extends('layouts.auth')

@section('title', __('dashboard.auth.confirm_title'))

@section('content')
    <p>{{ __('dashboard.auth.confirm_help') }}</p>
    @include('dashboard.auth._errors')
    <form method="post" action="{{ route('dashboard.confirm.store') }}" class="ui-stack" novalidate>
        @csrf
        <div class="ui-field">
            <label class="ui-label" for="password">{{ __('dashboard.auth.password') }}</label>
            <input class="ui-input" id="password" name="password" type="password" dir="ltr" autocomplete="current-password" required>
        </div>
        <div class="ui-field">
            <label class="ui-label" for="code">{{ __('dashboard.auth.code') }}</label>
            <input class="ui-input" id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" dir="ltr" maxlength="12" required>
        </div>
        <button type="submit" class="ui-btn ui-btn--primary">{{ __('dashboard.auth.confirm') }}</button>
    </form>
@endsection
