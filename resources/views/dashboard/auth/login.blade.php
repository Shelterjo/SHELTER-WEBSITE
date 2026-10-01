@extends('layouts.auth')

@section('title', __('dashboard.auth.login_title'))

@section('content')
    @if (session('status') === 'session_expired')
        <div class="ui-alert ui-alert--info" role="status">{{ __('dashboard.auth.session_expired') }}</div>
    @endif
    @include('dashboard.auth._errors')
    <form method="post" action="{{ route('login.store') }}" class="ui-stack" novalidate>
        @csrf
        <div class="ui-field">
            <label class="ui-label" for="email">{{ __('dashboard.auth.email') }}</label>
            <input class="ui-input" id="email" name="email" type="email" dir="ltr" autocomplete="username" required
                   value="{{ old('email') }}" @error('email') aria-invalid="true" aria-describedby="form-errors" @enderror>
        </div>
        <div class="ui-field">
            <label class="ui-label" for="password">{{ __('dashboard.auth.password') }}</label>
            <input class="ui-input" id="password" name="password" type="password" dir="ltr" autocomplete="current-password" required>
        </div>
        <button type="submit" class="ui-btn ui-btn--primary">{{ __('dashboard.auth.continue') }}</button>
    </form>
@endsection
