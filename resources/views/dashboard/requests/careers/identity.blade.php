@extends('layouts.dashboard')

@section('title', __('dashboard.requests.identity_title'))

@section('content')
    {{-- The full identity number, on its own page after re-confirmation; recorded, never cached (RECRUITMENT-SECURITY §5). --}}
    <x-ui.page-header :title="__('dashboard.requests.identity_title')" :description="$application->reference_number" />
    <x-ui.alert variant="warning">{{ __('dashboard.requests.identity_warning') }}</x-ui.alert>
    <dl class="ui-facts ui-record__section">
        <div>
            <dt>{{ __('dashboard.requests.options.id_type.'.$application->identity?->id_type) }}</dt>
            <dd class="ui-record__number"><bdi dir="ltr">{{ $number }}</bdi></dd>
        </div>
    </dl>
    <x-ui.button variant="outline" :href="route('dashboard.careers.show', $application)">{{ __('dashboard.requests.back_to_application') }}</x-ui.button>
@endsection
