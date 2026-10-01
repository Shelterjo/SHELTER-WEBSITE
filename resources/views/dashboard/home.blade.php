@extends('layouts.dashboard')

@section('title', __('dashboard.command_center'))

@section('content')
    <h1>{{ __('dashboard.command_center') }}</h1>
    {{-- PHASE 3: status tiles, active campaign, new counts, needs-attention queue (FINAL-ARCHITECTURE-REVIEW §10). --}}
@endsection
