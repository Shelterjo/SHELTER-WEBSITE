@extends('layouts.auth')

@section('title', __('dashboard.auth.confirm_title'))

@section('content')
    <p>{{ __('dashboard.auth.confirm_help') }}</p>
    @include('dashboard.auth._errors')
    <form method="post" action="{{ route('dashboard.confirm.store') }}" class="ui-stack" novalidate>
        @csrf
        <x-ui.field :label="__('dashboard.auth.password')" for="password" :error="$errors->first('password')" required>
            <x-ui.input type="password" name="password" dir="ltr" autocomplete="current-password" />
        </x-ui.field>
        <x-ui.field :label="__('dashboard.auth.code')" for="code" :error="$errors->first('code')" required>
            <x-ui.input name="code" inputmode="numeric" autocomplete="one-time-code" dir="ltr" maxlength="12" />
        </x-ui.field>
        <x-ui.button type="submit">{{ __('dashboard.auth.confirm') }}</x-ui.button>
    </form>
@endsection
