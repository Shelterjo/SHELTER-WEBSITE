@extends('layouts.dashboard')

@section('title', $member->exists ? ($member->display_name_ar ?? __('dashboard.team.title')) : __('dashboard.team.new_title'))

@section('content')
    {{--
        Add / edit a SHELTER Family profile (TeamEditor): visibility, name and job title in both languages, branch,
        optional bio and join date (each with its own switch), photo — and the person's recorded consent.
    --}}
    @php
        $hasOld = session()->hasOldInput();
        $checked = fn (string $name, bool $stored): bool => $hasOld ? (bool) old($name) : $stored;
        $published = (string) old('is_published', $member->is_published ? '1' : '0');
        $title = $member->exists ? ((app()->getLocale() === 'ar' ? $member->display_name_ar : $member->display_name_en) ?? $member->display_name_ar ?? __('dashboard.team.title')) : __('dashboard.team.new_title');
        $date = fn ($value) => $value?->timezone('Asia/Amman')->format('Y-m-d');
    @endphp
    <x-ui.page-header :title="$title">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.team.index')">{{ __('dashboard.team.title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($errors->any())
        <x-ui.error-summary :errors="$errors" :title="__('dashboard.pages.errors.summary')" />
    @endif

    <form class="ui-editor" method="post" action="{{ $member->exists ? route('dashboard.team.update', $member) : route('dashboard.team.store') }}">
        @csrf
        @if ($member->exists)
            @method('PUT')
        @endif

        <x-ui.fieldset :legend="__('dashboard.team.fields.published')" id="is_published">
            <div class="ui-editor__options">
                @foreach (['0', '1'] as $option)
                    <x-ui.radio :label="__('dashboard.team.published_options.'.$option)" name="is_published" :value="$option" :id="'published-'.$option" :checked="$published === $option" />
                @endforeach
            </div>
        </x-ui.fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.team.groups.profile') }}</legend>
            <div class="ui-bilingual">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    <div class="ui-bilingual__column">
                        <p class="ui-bilingual__language">{{ __('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english')) }}</p>
                        <x-ui.field :label="__('dashboard.team.fields.display_name')" :for="'display_name_'.$locale" :error="$errors->first('display_name_'.$locale)">
                            <x-ui.input :id="'display_name_'.$locale" :name="'display_name_'.$locale" :value="old('display_name_'.$locale, $member->{'display_name_'.$locale})" maxlength="120" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                        <x-ui.field :label="__('dashboard.team.fields.job_title')" :for="'job_title_'.$locale" :error="$errors->first('job_title_'.$locale)">
                            <x-ui.input :id="'job_title_'.$locale" :name="'job_title_'.$locale" :value="old('job_title_'.$locale, $member->{'job_title_'.$locale})" maxlength="120" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                    </div>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.team.groups.work') }}</legend>
            <div class="ui-editor__pair">
                <x-ui.field :label="__('dashboard.team.fields.branch')" for="branch_id" optional>
                    <x-ui.select id="branch_id" name="branch_id" :options="['' => __('dashboard.team.fields.no_branch')] + $branches" :selected="(string) old('branch_id', $member->branch_id)" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.team.fields.department')" for="department" :error="$errors->first('department')" optional>
                    <x-ui.input id="department" name="department" :value="old('department', $member->department)" maxlength="60" />
                </x-ui.field>
            </div>
            <div class="ui-editor__pair">
                <x-ui.field :label="__('dashboard.team.fields.join_date')" for="join_date" :error="$errors->first('join_date')" optional>
                    <x-ui.input type="date" id="join_date" name="join_date" :value="old('join_date', $member->join_date?->format('Y-m-d'))" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.team.fields.sort_order')" for="sort_order" optional>
                    <x-ui.input type="number" id="sort_order" name="sort_order" inputmode="numeric" min="0" max="999" :value="old('sort_order', $member->sort_order ?? 0)" />
                </x-ui.field>
            </div>
            <x-ui.checkbox :label="__('dashboard.team.fields.show_join_date')" name="show_join_date" value="1" id="show_join_date" :checked="$checked('show_join_date', (bool) $member->show_join_date)" />
        </fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.team.groups.bio') }}</legend>
            <div class="ui-bilingual">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    <div class="ui-bilingual__column">
                        <x-ui.field :label="__('dashboard.team.fields.bio').' — '.__('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english'))" :for="'bio_'.$locale" :error="$errors->first('bio_'.$locale)" optional>
                            <x-ui.textarea :id="'bio_'.$locale" :name="'bio_'.$locale" :value="old('bio_'.$locale, $member->{'bio_'.$locale})" rows="3" maxlength="600" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                    </div>
                @endforeach
            </div>
            <x-ui.checkbox :label="__('dashboard.team.fields.show_bio')" name="show_bio" value="1" id="show_bio" :checked="$checked('show_bio', (bool) $member->show_bio)" />
        </fieldset>

        <div class="ui-editor__group">
            @include('dashboard.media._picker', [
                'name' => 'photo_media_id', 'id' => 'photo_media_id', 'selected' => old('photo_media_id', $member->photo_media_id), 'images' => $images,
                'legend' => __('dashboard.team.fields.photo'), 'none' => __('dashboard.team.fields.no_photo'), 'empty' => __('dashboard.team.fields.no_photos'),
            ])
        </div>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.team.groups.consent') }}</legend>
            <p class="ui-note">{{ __('dashboard.team.consent_help') }}</p>
            <div class="ui-editor__pair">
                <x-ui.field :label="__('dashboard.team.fields.consent_at')" for="publish_consent_at" :error="$errors->first('publish_consent_at')">
                    <x-ui.input type="date" id="publish_consent_at" name="publish_consent_at" :value="old('publish_consent_at', $date($member->publish_consent_at))" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.team.fields.consent_ref')" for="publish_consent_version" :hint="__('dashboard.team.fields.consent_ref_hint')" :error="$errors->first('publish_consent_version')">
                    <x-ui.input id="publish_consent_version" name="publish_consent_version" :value="old('publish_consent_version', $member->publish_consent_version)" maxlength="40" />
                </x-ui.field>
            </div>
            <x-ui.field :label="__('dashboard.team.fields.withdrawn_at')" for="consent_withdrawn_at" :hint="__('dashboard.team.fields.withdrawn_hint')" :error="$errors->first('consent_withdrawn_at')" optional>
                <x-ui.input type="date" id="consent_withdrawn_at" name="consent_withdrawn_at" :value="old('consent_withdrawn_at', $date($member->consent_withdrawn_at))" />
            </x-ui.field>
        </fieldset>

        <div class="ui-editor__bar">
            <x-ui.button type="submit" size="lg">{{ __('dashboard.save') }}</x-ui.button>
        </div>
    </form>

    @if ($member->exists)
        <form class="ui-editor__aside" method="post" action="{{ route($member->archived_at === null ? 'dashboard.team.archive' : 'dashboard.team.restore', $member) }}">
            @csrf
            @if ($member->archived_at === null)
                <p class="ui-note">{{ __('dashboard.team.archive_help') }}</p>
                <x-ui.button type="submit" variant="outline" icon="inbox">{{ __('dashboard.team.archive') }}</x-ui.button>
            @else
                <x-ui.button type="submit" variant="outline" icon="rotate-cw">{{ __('dashboard.team.restore') }}</x-ui.button>
            @endif
        </form>
    @endif
@endsection
