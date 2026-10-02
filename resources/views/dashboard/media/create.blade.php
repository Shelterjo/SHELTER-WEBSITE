@extends('layouts.dashboard')

@section('title', __('dashboard.media.upload_title'))

@section('content')
    {{-- Upload: the files and the rights the Owner knows now; every new image waits for approval (MEDIA-003). --}}
    @php
        $sources = collect(\App\Models\Media::SOURCES)->mapWithKeys(fn ($s) => [$s => __('dashboard.media.sources.'.$s)])->all();
        $licenses = collect(\App\Models\Media::LICENSES)->mapWithKeys(fn ($l) => [$l => __('dashboard.media.licenses.'.$l)])->all();
        $summary = collect($errors->getMessages())->mapWithKeys(fn ($m, $k) => [($k === 'people_consent' ? 'people-none' : $k) => $m[0]])->all();
    @endphp
    <x-ui.page-header :title="__('dashboard.media.upload_title')" :description="__('dashboard.media.upload_description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.media.index')">{{ __('dashboard.media.title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($errors->any())
        <x-ui.error-summary :errors="$summary" :title="__('dashboard.pages.errors.summary')" />
    @endif

    <form class="ui-editor" method="post" action="{{ route('dashboard.media.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="ui-editor__group">
            <x-ui.field :label="__('dashboard.media.fields.files')" for="files" :hint="__('dashboard.media.fields.files_hint', ['count' => \App\Services\Dashboard\MediaEditor::MAX_FILES, 'mb' => \App\Services\Dashboard\MediaEditor::MAX_KB / 1024])" :error="$errors->first('files')" required>
                <x-ui.input type="file" id="files" name="files[]" multiple accept="image/jpeg,image/png,image/webp" />
            </x-ui.field>
        </div>
        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.media.groups.rights') }}</legend>
            <x-ui.field :label="__('dashboard.media.fields.source')" for="source" :error="$errors->first('source')" required>
                <x-ui.select id="source" name="source" :options="$sources" :selected="old('source')" :placeholder="__('ui.select_placeholder')" />
            </x-ui.field>
            <x-ui.fieldset :legend="__('dashboard.media.fields.people')" id="people-group" :error="$errors->first('people_consent')" required>
                @foreach (['none', 'not_recorded'] as $option)
                    <x-ui.radio :label="__('dashboard.media.people_options.'.$option)" name="people_consent" :value="$option" :id="'people-'.$option" :checked="old('people_consent') === $option" />
                @endforeach
            </x-ui.fieldset>
            <x-ui.field :label="__('dashboard.media.fields.photographer')" for="photographer" :error="$errors->first('photographer')" optional>
                <x-ui.input id="photographer" name="photographer" :value="old('photographer')" maxlength="150" />
            </x-ui.field>
            <x-ui.field :label="__('dashboard.media.fields.rights_holder')" for="rights_holder" :hint="__('dashboard.media.fields.rights_holder_hint')" :error="$errors->first('rights_holder')" optional>
                <x-ui.input id="rights_holder" name="rights_holder" :value="old('rights_holder')" maxlength="150" />
            </x-ui.field>
            <x-ui.field :label="__('dashboard.media.fields.license')" for="license">
                <x-ui.select id="license" name="license" :options="$licenses" :selected="old('license', 'full')" />
            </x-ui.field>
        </fieldset>
        <div class="ui-editor__bar">
            <x-ui.button type="submit" size="lg" icon="upload">{{ __('dashboard.media.upload') }}</x-ui.button>
        </div>
    </form>
@endsection
