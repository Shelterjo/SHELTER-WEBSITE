@extends('layouts.dashboard')

@section('title', $media->code)

@section('content')
    {{--
        One image (MediaEditor): the preview, whether it is on the site now and why not, where it is used — then the
        Owner's decision, its description in both languages, its rights, the people in it, and the press kit.
    --}}
    @php
        $hasOld = session()->hasOldInput();
        $checked = fn (string $name, bool $stored): bool => $hasOld ? (bool) old($name) : $stored;
        $decision = old('approval', match ($media->approval_status) { \App\Models\Media::APPROVED => 'approved', \App\Models\Media::REJECTED => 'rejected', default => 'pending' });
        $sources = collect(\App\Models\Media::SOURCES)->mapWithKeys(fn ($s) => [$s => __('dashboard.media.sources.'.$s)])->all();
        $licenses = collect(\App\Models\Media::LICENSES)->mapWithKeys(fn ($l) => [$l => __('dashboard.media.licenses.'.$l)])->all();
        $focal = fn (string $axis) => collect(\App\Services\Dashboard\MediaEditor::FOCAL)->mapWithKeys(fn ($v) => [$v => __('dashboard.media.focal.'.$axis.'.'.$v)])->all();
        $restricted = in_array(old('source', $media->source), \App\Models\Media::RESTRICTED_SOURCES, true);
        $summary = collect($errors->getMessages())->mapWithKeys(fn ($m, $k) => [
            (string) preg_replace(['/^people\.(\d+)\.(\w+)$/', '/^source_explicitly_approved$/', '/^people$/'], ['p$1-$2', 'explicit', 'people-group'], $k) => $m[0],
        ])->all();
        $w = min(960, $media->width ?? 960);
        $h = $media->width ? (int) round(($media->height ?? $media->width) * $w / $media->width) : $w;
        $needsAlt = blank($media->alt_ar) || blank($media->alt_en);
    @endphp
    <x-ui.page-header :title="$media->code">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.media.index')">{{ __('dashboard.media.title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($errors->any())
        <x-ui.error-summary :errors="$summary" :title="__('dashboard.pages.errors.summary')" />
    @endif

    <div class="ui-media-summary">
        <img class="ui-media-summary__image" src="{{ route('dashboard.media.preview', $media) }}" alt="{{ $media->alt(app()->getLocale()) ?? '' }}" width="{{ $w }}" height="{{ $h }}">
        <div class="ui-media-summary__facts">
            <section class="ui-status-box" aria-labelledby="status-title">
                <h2 class="ui-status-box__title" id="status-title">{{ __('dashboard.media.status_title') }}</h2>
                @if ($live && ! $needsAlt)
                    <p class="ui-status-box__yes"><x-ui.icon name="circle-check" size="sm" /><span>{{ __('dashboard.media.status_live') }}</span></p>
                @else
                    <p>{{ __('dashboard.media.status_not_live') }}</p>
                    <ul class="ui-status-box__list">
                        @foreach ($problems as $problem)
                            <li>{{ __('dashboard.media.problems.'.$problem) }}</li>
                        @endforeach
                        @if ($needsAlt)
                            <li>{{ __('dashboard.media.problems.alt') }}</li>
                        @endif
                    </ul>
                @endif
            </section>
            <dl class="ui-facts">
                <div><dt>{{ __('dashboard.media.info.code') }}</dt><dd><bdi>{{ $media->code }}</bdi></dd></div>
                <div><dt>{{ __('dashboard.media.info.size') }}</dt><dd><bdi>{{ $media->width }} × {{ $media->height }}</bdi></dd></div>
                <div><dt>{{ __('dashboard.media.info.uploaded') }}</dt><dd><bdi>{{ $media->created_at?->timezone('Asia/Amman')->format('Y-m-d') }}</bdi></dd></div>
                <div><dt>{{ __('dashboard.media.used_title') }}</dt><dd>{{ $usedIn === [] ? __('dashboard.media.used_none') : implode(' · ', $usedIn) }}</dd></div>
            </dl>
        </div>
    </div>

    <form class="ui-editor" method="post" action="{{ route('dashboard.media.update', $media) }}">
        @csrf
        @method('PUT')

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.media.groups.decision') }}</legend>
            <x-ui.fieldset :legend="__('dashboard.media.fields.approval')" id="approval">
                <div class="ui-editor__options">
                    @foreach (['pending', 'approved', 'rejected'] as $option)
                        <x-ui.radio :label="__('dashboard.media.decisions.'.$option)" name="approval" :value="$option" :id="'approval-'.$option" :checked="$decision === $option" />
                    @endforeach
                </div>
            </x-ui.fieldset>
            <x-ui.fieldset :legend="__('dashboard.media.fields.channels')" id="channels">
                <div class="ui-editor__options">
                    <x-ui.checkbox :label="__('dashboard.media.fields.ok_website')" name="ok_website" value="1" id="ok_website" :checked="$checked('ok_website', $media->ok_website)" />
                    <x-ui.checkbox :label="__('dashboard.media.fields.ok_ads')" name="ok_ads" value="1" id="ok_ads" :checked="$checked('ok_ads', $media->ok_ads)" />
                </div>
            </x-ui.fieldset>
            <x-ui.field :label="__('dashboard.media.fields.expires')" for="rights_expires_at" :hint="__('dashboard.media.fields.expires_hint')" :error="$errors->first('rights_expires_at')" optional>
                <x-ui.input type="date" id="rights_expires_at" name="rights_expires_at" :value="old('rights_expires_at', $media->rights_expires_at?->timezone('Asia/Amman')->format('Y-m-d'))" />
            </x-ui.field>
            @if ($restricted)
                <x-ui.alert variant="warning">{{ __('dashboard.media.restricted_help') }}</x-ui.alert>
                <x-ui.checkbox :label="__('dashboard.media.fields.explicit')" name="source_explicitly_approved" value="1" id="explicit"
                    :checked="$checked('source_explicitly_approved', $media->source_explicitly_approved)" :error="$errors->first('source_explicitly_approved')" />
            @endif
        </fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.media.groups.text') }}</legend>
            <div class="ui-bilingual">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    <div class="ui-bilingual__column">
                        <x-ui.field :label="__('dashboard.media.fields.alt_'.$locale)" :for="'alt_'.$locale" :hint="__('dashboard.media.fields.alt_hint')" :error="$errors->first('alt_'.$locale)">
                            <x-ui.textarea :id="'alt_'.$locale" :name="'alt_'.$locale" :value="old('alt_'.$locale, $media->{'alt_'.$locale})" rows="2" maxlength="300" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                    </div>
                @endforeach
            </div>
            <div class="ui-editor__pair">
                <x-ui.field :label="__('dashboard.media.fields.focal_x')" for="focal_x" :hint="__('dashboard.media.fields.focal_hint')">
                    <x-ui.select id="focal_x" name="focal_x" :options="$focal('x')" :selected="(string) old('focal_x', $media->focal_x)" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.media.fields.focal_y')" for="focal_y">
                    <x-ui.select id="focal_y" name="focal_y" :options="$focal('y')" :selected="(string) old('focal_y', $media->focal_y)" />
                </x-ui.field>
            </div>
        </fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.media.groups.rights') }}</legend>
            <x-ui.field :label="__('dashboard.media.fields.source')" for="source" :error="$errors->first('source')" required>
                <x-ui.select id="source" name="source" :options="$sources" :selected="old('source', $media->source)" />
            </x-ui.field>
            <div class="ui-editor__pair">
                <x-ui.field :label="__('dashboard.media.fields.photographer')" for="photographer" :error="$errors->first('photographer')" optional>
                    <x-ui.input id="photographer" name="photographer" :value="old('photographer', $media->photographer)" maxlength="150" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.media.fields.rights_holder')" for="rights_holder" :hint="__('dashboard.media.fields.rights_holder_hint')" :error="$errors->first('rights_holder')" optional>
                    <x-ui.input id="rights_holder" name="rights_holder" :value="old('rights_holder', $media->rights_holder)" maxlength="150" />
                </x-ui.field>
            </div>
            <x-ui.field :label="__('dashboard.media.fields.license')" for="license">
                <x-ui.select id="license" name="license" :options="$licenses" :selected="old('license', $media->license)" />
            </x-ui.field>
            <x-ui.field :label="__('dashboard.media.fields.license_note')" for="license_note" :error="$errors->first('license_note')" optional>
                <x-ui.input id="license_note" name="license_note" :value="old('license_note', $media->license_note)" maxlength="300" />
            </x-ui.field>
            <x-ui.field :label="__('dashboard.media.fields.restrictions')" for="restrictions" :hint="__('dashboard.media.fields.restrictions_hint')" :error="$errors->first('restrictions')" optional>
                <x-ui.textarea id="restrictions" name="restrictions" :value="old('restrictions', $media->restrictions)" rows="2" maxlength="500" />
            </x-ui.field>
        </fieldset>

        <fieldset class="ui-editor__group ui-people" id="people-group">
            <legend class="ui-editor__legend">{{ __('dashboard.media.groups.people') }}</legend>
            <x-ui.fieldset :legend="__('dashboard.media.fields.people')" id="people-consent" :error="$errors->first('people')">
                @foreach (\App\Models\Media::PEOPLE as $option)
                    <x-ui.radio :label="__('dashboard.media.people_options.'.$option)" name="people_consent" :value="$option" :id="'people-'.$option" :checked="old('people_consent', $media->people_consent) === $option" />
                @endforeach
            </x-ui.fieldset>
            <p class="ui-note ui-people__details">{{ __('dashboard.media.people_help') }}</p>
            <div class="ui-editor__sections ui-people__details">
                @foreach ($people as $index => $row)
                    @include('dashboard.media._person', ['i' => $index, 'row' => $row, 'blank' => collect($row)->filter(fn ($v) => is_string($v) && trim($v) !== '')->isEmpty()])
                @endforeach
            </div>
        </fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.media.groups.use') }}</legend>
            <x-ui.checkbox :label="__('dashboard.media.fields.press_kit')" name="press_kit" value="1" id="press_kit" :checked="$checked('press_kit', $pressKit)" />
        </fieldset>

        <div class="ui-editor__bar">
            <x-ui.button type="submit" size="lg">{{ __('dashboard.save') }}</x-ui.button>
        </div>
    </form>

    <form class="ui-editor__aside" method="post" action="{{ route($media->archived_at === null ? 'dashboard.media.archive' : 'dashboard.media.restore', $media) }}">
        @csrf
        @if ($media->archived_at === null)
            <p class="ui-note">{{ __('dashboard.media.archive_help') }}</p>
            <x-ui.button type="submit" variant="outline" icon="inbox">{{ __('dashboard.media.archive') }}</x-ui.button>
        @else
            <x-ui.button type="submit" variant="outline" icon="rotate-cw">{{ __('dashboard.media.restore') }}</x-ui.button>
        @endif
    </form>
@endsection
