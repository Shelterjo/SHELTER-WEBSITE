@extends('layouts.auth')

@section('title', __('dashboard.auth.recovery_title'))

@section('content')
    <x-ui.alert variant="warning">{{ __('dashboard.auth.recovery_warning') }}</x-ui.alert>
    <ul class="ui-code-list" role="list" dir="ltr">
        @foreach ($codes as $code)
            <li><code>{{ $code }}</code></li>
        @endforeach
    </ul>
    <x-ui.button :href="route('dashboard.home')">{{ __('dashboard.auth.recovery_done') }}</x-ui.button>
@endsection
