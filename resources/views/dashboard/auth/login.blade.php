@extends('layouts.auth')

@section('title', __('dashboard.auth.login_title'))

@section('content')
    @if (session('status') === 'session_expired')
        <x-ui.alert variant="info">{{ __('dashboard.auth.session_expired') }}</x-ui.alert>
    @endif
    @include('dashboard.auth._errors')
    <form method="post" action="{{ route('login.store') }}" class="ui-stack" novalidate>
        @csrf
        <x-ui.field :label="__('dashboard.auth.email')" for="email" :error="$errors->first('email')" required>
            <x-ui.input type="email" name="email" dir="ltr" autocomplete="username" :value="old('email')" />
        </x-ui.field>
        <x-ui.field :label="__('dashboard.auth.password')" for="password" required>
            <x-ui.input type="password" name="password" dir="ltr" autocomplete="current-password" />
        </x-ui.field>
        <x-ui.button type="submit">{{ __('dashboard.auth.continue') }}</x-ui.button>
    </form>
@endsection
