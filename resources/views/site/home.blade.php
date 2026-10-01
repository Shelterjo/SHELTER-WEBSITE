@extends('layouts.site')

@section('content')
    {{-- Home sections (D-013 actions: menu, locations, branch info) arrive in PHASE 2. The language switch lives in the header. --}}
    <div class="ui-container ui-stack">
        <h1 lang="en" dir="ltr">{{ __('site.brand') }}</h1>
    </div>
@endsection
