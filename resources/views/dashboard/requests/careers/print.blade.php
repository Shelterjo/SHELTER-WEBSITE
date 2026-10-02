@extends('layouts.dashboard')

@section('title', __('dashboard.requests.export.print_title'))

@section('content')
    {{--
        The PDF export (CAREERS-072): a print-ready table — "Print / Save as PDF" in the browser shapes Arabic correctly
        (no server-side PDF library needed). The dashboard chrome is hidden when printing (print.css rules in shell.css).
    --}}
    <x-ui.page-header :title="__('dashboard.requests.export.print_title')" :description="trans_choice('dashboard.requests.results', count($rows) - 1, ['count' => count($rows) - 1]).' · '.now('Asia/Amman')->format('Y-m-d H:i')">
        <x-slot:actions>
            <x-ui.button type="button" icon="file-text" data-print>{{ __('dashboard.requests.export.print') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>
    @if ($full)
        <x-ui.alert variant="warning">{{ __('dashboard.requests.export.full_warning') }}</x-ui.alert>
    @endif
    {{-- One card per application (all its fields as label / value) — reads well on A4, never cut at the page edge. --}}
    <div class="ui-print-list">
        @foreach (array_slice($rows, 1) as $row)
            <section class="ui-print-item" aria-labelledby="p{{ $loop->index }}">
                <h2 class="ui-print-item__title" id="p{{ $loop->index }}"><bdi>{{ $row[0] }}</bdi> · {{ $row[3] }}</h2>
                <dl class="ui-print-item__facts">
                    @foreach ($row as $i => $cell)
                        @if (! in_array($i, [0, 3], true) && $cell !== '')
                            <div>
                                <dt>{{ $rows[0][$i] }}</dt>
                                <dd><bdi>{{ $cell }}</bdi></dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </section>
        @endforeach
    </div>
@endsection
