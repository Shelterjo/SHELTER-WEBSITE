@extends('layouts.dashboard')

@section('title', __('dashboard.attention.title'))

@section('content')
    {{-- Needs attention (App\Services\Core\Attention): open issues only — never a feed of every event. --}}
    @php $A = 'dashboard.attention.'; @endphp
    <x-ui.page-header :title="__($A.'title')" :description="__($A.'description')" />

    @if ($items === [])
        <x-ui.empty-state :title="__($A.'empty_title')">{{ __($A.'empty_text') }}</x-ui.empty-state>
    @else
        @include('dashboard.partials.attention-list', ['items' => $items])
    @endif
@endsection
