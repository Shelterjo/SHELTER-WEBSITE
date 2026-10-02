@extends('layouts.dashboard')

@section('title', __('dashboard.requests.bulk.confirm_title'))

@section('content')
    {{-- One clear confirmation for a status change on several applications (CAREERS-069). --}}
    @php $label = __('dashboard.requests.statuses.'.$status); @endphp
    <x-ui.page-header :title="__('dashboard.requests.bulk.confirm_title')" />
    <section class="ui-record__section ui-record__section--status" aria-labelledby="bulk-question">
        <h2 id="bulk-question" class="ui-record__title">{{ trans_choice('dashboard.requests.bulk.question', count($ids), ['count' => count($ids), 'status' => $label]) }}</h2>
        @if ($status === 'archived')
            <p class="ui-note">{{ __('dashboard.requests.bulk.archive_note') }}</p>
        @endif
        <ul class="ui-record__lines" role="list">
            @foreach ($applications as $application)
                <li><bdi>{{ $application->reference_number }}</bdi> · {{ $application->job?->full_name }} · {{ __('dashboard.requests.statuses.'.$application->status) }}</li>
            @endforeach
        </ul>
        <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.bulk.apply') }}">
            @csrf
            <input type="hidden" name="status" value="{{ $status }}">
            @foreach ($ids as $id)
                <input type="hidden" name="ids[]" value="{{ $id }}">
            @endforeach
            <x-ui.field :label="__('dashboard.requests.note_optional')" for="bulk-note" optional>
                <x-ui.input id="bulk-note" name="note" maxlength="1000" />
            </x-ui.field>
            <div class="ui-record__archive">
                <x-ui.button type="submit">{{ __('dashboard.requests.bulk.confirm', ['status' => $label]) }}</x-ui.button>
                <x-ui.button variant="ghost" :href="$back">{{ __('dashboard.requests.bulk.cancel') }}</x-ui.button>
            </div>
        </form>
    </section>
@endsection
