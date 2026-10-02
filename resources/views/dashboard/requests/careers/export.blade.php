@extends('layouts.dashboard')

@section('title', __('dashboard.requests.export.title'))

@section('content')
    {{--
        Export (CAREERS-072, INFRA-036): what to export, in which format, identity numbers masked unless the Owner asks
        for them explicitly. Pressing the button is the explicit confirmation; every export is audited. The attachments
        are a separate ZIP (CAREERS-073), never inside an export.
    --}}
    @php $E = 'dashboard.requests.export.'; @endphp
    <x-ui.page-header :title="__($E.'title')" :description="__($E.'description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.careers.index')">{{ __('dashboard.requests.careers_title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($zip)
        <section class="ui-record__section" aria-labelledby="zip-title">
            <h2 id="zip-title" class="ui-record__title">{{ __($E.'zip_title') }}</h2>
            <p class="ui-note">{{ __($E.'zip_help', ['max' => $zipMax]) }}</p>
            <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.zip') }}">
                @csrf
                @foreach ($ids as $id)
                    <input type="hidden" name="ids[]" value="{{ $id }}">
                @endforeach
                <x-ui.button type="submit" icon="upload" :disabled="count($ids) > $zipMax">{{ trans_choice($E.'zip_button', count($ids), ['count' => count($ids)]) }}</x-ui.button>
            </form>
        </section>
    @endif

    <form class="ui-editor" method="post" action="{{ route('dashboard.careers.export.run') }}">
        @csrf
        @foreach ($ids as $id)
            <input type="hidden" name="ids[]" value="{{ $id }}">
        @endforeach
        @foreach ($filters as $key => $value)
            <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
        @endforeach
        <x-ui.fieldset :legend="__($E.'what')" id="scope">
            <div class="ui-editor__options">
                @foreach (['filtered', 'selected', 'all'] as $option)
                    @if ($option !== 'selected' || $counts['selected'] > 0)
                        <x-ui.radio :label="trans_choice($E.'scopes.'.$option, $counts[$option], ['count' => $counts[$option]])" name="scope" :value="$option" :id="'scope-'.$option" :checked="$scope === $option" />
                    @endif
                @endforeach
            </div>
        </x-ui.fieldset>
        <x-ui.fieldset :legend="__($E.'format')" id="format">
            <div class="ui-editor__options">
                @foreach (['xlsx', 'csv', 'print'] as $format)
                    <x-ui.radio :label="__($E.'formats.'.$format)" name="format" :value="$format" :id="'format-'.$format" :checked="$format === 'xlsx'" :hint="__($E.'format_hints.'.$format)" />
                @endforeach
            </div>
        </x-ui.fieldset>
        <x-ui.checkbox :label="__($E.'full_identity')" name="full_identity" value="1" id="full_identity" :hint="__($E.'full_identity_hint')" />
        <div class="ui-editor__bar">
            <x-ui.button type="submit" size="lg" icon="upload">{{ __($E.'run') }}</x-ui.button>
        </div>
    </form>
@endsection
