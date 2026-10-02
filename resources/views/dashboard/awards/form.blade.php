@extends('layouts.dashboard')

@section('title', $award->exists ? ($award->title_ar ?? __('dashboard.awards.title')) : __('dashboard.awards.new_title'))

@section('content')
    {{--
        Add / edit an award (AwardEditor): status, the text in both languages, year, proof link, image — and, to
        publish, the Owner's confirmation that it is real (recorded in the Fact Registry).
    --}}
    @php
        $status = old('status', $award->status === 'published' ? 'published' : 'draft');
        $title = $award->exists ? ((app()->getLocale() === 'ar' ? $award->title_ar : $award->title_en) ?? $award->title_ar ?? __('dashboard.awards.title')) : __('dashboard.awards.new_title');
    @endphp
    <x-ui.page-header :title="$title">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.awards.index')">{{ __('dashboard.awards.title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($errors->any())
        <x-ui.error-summary :errors="$errors" :title="__('dashboard.pages.errors.summary')" />
    @endif

    <form class="ui-editor" method="post" action="{{ $award->exists ? route('dashboard.awards.update', $award) : route('dashboard.awards.store') }}">
        @csrf
        @if ($award->exists)
            @method('PUT')
        @endif

        <x-ui.fieldset :legend="__('dashboard.awards.fields.status')" id="status">
            <div class="ui-editor__options">
                @foreach (['draft', 'published'] as $option)
                    <x-ui.radio :label="__('dashboard.awards.statuses.'.$option)" name="status" :value="$option" :id="'status-'.$option" :checked="$status === $option" />
                @endforeach
            </div>
        </x-ui.fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.media.groups.text') }}</legend>
            <div class="ui-bilingual">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    <div class="ui-bilingual__column">
                        <p class="ui-bilingual__language">{{ __('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english')) }}</p>
                        <x-ui.field :label="__('dashboard.awards.fields.title')" :for="'title_'.$locale" :error="$errors->first('title_'.$locale)">
                            <x-ui.input :id="'title_'.$locale" :name="'title_'.$locale" :value="old('title_'.$locale, $award->{'title_'.$locale})" maxlength="200" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                        <x-ui.field :label="__('dashboard.awards.fields.issuer')" :for="'issuer_'.$locale" :error="$errors->first('issuer_'.$locale)">
                            <x-ui.input :id="'issuer_'.$locale" :name="'issuer_'.$locale" :value="old('issuer_'.$locale, $award->{'issuer_'.$locale})" maxlength="200" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                        <x-ui.field :label="__('dashboard.awards.fields.description')" :for="'description_'.$locale" :error="$errors->first('description_'.$locale)" optional>
                            <x-ui.textarea :id="'description_'.$locale" :name="'description_'.$locale" :value="old('description_'.$locale, $award->{'description_'.$locale})" rows="3" maxlength="600" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                    </div>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.awards.fields.year') }} · {{ __('dashboard.awards.fields.evidence_url') }}</legend>
            <div class="ui-editor__pair">
                <x-ui.field :label="__('dashboard.awards.fields.year')" for="year" :error="$errors->first('year')">
                    <x-ui.input type="number" id="year" name="year" inputmode="numeric" :min="\App\Services\Dashboard\AwardEditor::FIRST_YEAR" :max="now()->year" :value="old('year', $award->year)" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.awards.fields.sort')" for="sort" optional>
                    <x-ui.input type="number" id="sort" name="sort" inputmode="numeric" min="0" max="999" :value="old('sort', $award->sort ?? 0)" />
                </x-ui.field>
            </div>
            <x-ui.field :label="__('dashboard.awards.fields.evidence_url')" for="evidence_url" :hint="__('dashboard.awards.fields.evidence_hint')" :error="$errors->first('evidence_url')" optional>
                <x-ui.input type="url" id="evidence_url" name="evidence_url" inputmode="url" dir="ltr" :value="old('evidence_url', $award->evidence_url)" maxlength="500" />
            </x-ui.field>
        </fieldset>

        <div class="ui-editor__group">
            @include('dashboard.media._picker', [
                'name' => 'media_id', 'id' => 'media_id', 'selected' => old('media_id', $award->media_id), 'images' => $images,
                'legend' => __('dashboard.awards.fields.image'), 'none' => __('dashboard.awards.fields.no_image'), 'empty' => __('dashboard.awards.fields.no_images'),
            ])
        </div>

        <div class="ui-editor__group">
            @if ($confirmed)
                <x-ui.alert variant="success">{{ __('dashboard.awards.confirmed_note') }}</x-ui.alert>
            @endif
            <x-ui.checkbox :label="__('dashboard.awards.fields.confirm')" name="confirm" value="1" id="confirm" :hint="__('dashboard.awards.fields.confirm_hint')" :error="$errors->first('confirm')" :checked="(bool) old('confirm')" />
        </div>

        <div class="ui-editor__bar">
            <x-ui.button type="submit" size="lg">{{ __('dashboard.save') }}</x-ui.button>
        </div>
    </form>

    @if ($award->exists)
        <form class="ui-editor__aside" method="post" action="{{ route($award->archived_at === null ? 'dashboard.awards.archive' : 'dashboard.awards.restore', $award) }}">
            @csrf
            @if ($award->archived_at === null)
                <p class="ui-note">{{ __('dashboard.awards.archive_help') }}</p>
                <x-ui.button type="submit" variant="outline" icon="inbox">{{ __('dashboard.awards.archive') }}</x-ui.button>
            @else
                <x-ui.button type="submit" variant="outline" icon="rotate-cw">{{ __('dashboard.awards.restore') }}</x-ui.button>
            @endif
        </form>
    @endif
@endsection
