@extends('layouts.dashboard')

@section('title', __('dashboard.requests.delete_title', ['reference' => $application->reference_number]))

@section('content')
    {{-- Permanent deletion (CAREERS-070): archived only, after re-confirmation, typing the number — one at a time. --}}
    <x-ui.page-header :title="__('dashboard.requests.delete_title', ['reference' => $application->reference_number])" />
    @if ($errors->any())
        <x-ui.error-summary :errors="$errors" :title="__('dashboard.pages.errors.summary')" />
    @endif
    <x-ui.alert variant="danger">{{ __('dashboard.requests.delete_will') }}</x-ui.alert>
    <p class="ui-note">{{ __('dashboard.requests.delete_kept') }}</p>
    <form class="ui-editor ui-record__danger" method="post" action="{{ route('dashboard.careers.destroy', $application) }}">
        @csrf
        @method('DELETE')
        <x-ui.field :label="__('dashboard.requests.delete_type', ['reference' => $application->reference_number])" for="reference" :error="$errors->first('reference')">
            <x-ui.input id="reference" name="reference" autocomplete="off" dir="ltr" />
        </x-ui.field>
        <x-ui.checkbox :label="__('dashboard.requests.delete_understood')" name="understood" value="1" id="understood" />
        <div class="ui-record__archive">
            <x-ui.button type="submit" variant="danger" icon="circle-x">{{ __('dashboard.requests.delete') }}</x-ui.button>
            <x-ui.button variant="ghost" :href="route('dashboard.careers.show', $application)">{{ __('dashboard.requests.delete_cancel') }}</x-ui.button>
        </div>
    </form>
@endsection
