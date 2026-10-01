@extends('layouts.auth')

@section('title', __('dashboard.auth.recovery_title'))

@section('content')
    <div class="ui-alert ui-alert--warning" role="status">{{ __('dashboard.auth.recovery_warning') }}</div>
    <ul class="ui-codes" role="list" dir="ltr">
        @foreach ($codes as $code)
            <li><code>{{ $code }}</code></li>
        @endforeach
    </ul>
    <a class="ui-btn ui-btn--primary" href="{{ route('dashboard.home') }}">{{ __('dashboard.auth.recovery_done') }}</a>
@endsection
