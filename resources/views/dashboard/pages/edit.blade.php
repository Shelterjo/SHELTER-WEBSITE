@extends('layouts.dashboard')

@section('title', $label)

@section('content')
    {{--
        Page editor (PageEditor): status, the page's own text in both languages, then its sections. Works without
        JavaScript (a blank section slot at the end adds one; a number orders them); with it, "Add a section" and
        up/down buttons. Publishing is refused with the exact reason when something is missing in one language.
    --}}
    @php
        $field = fn (string $name) => old($name, $page->{$name});
        $status = old('status', $page->status?->value ?? 'draft');
        // Summary links point at the field or section they name (sections.2 → #section-2, sections.2.body_en → #s2-body-en).
        $summary = collect($errors->getMessages())->mapWithKeys(fn ($m, $k) => [
            (string) preg_replace(['/^sections\.(\d+)\.(heading|body)_(ar|en)$/', '/^sections\.(\d+)$/'], ['s$1-$2-$3', 'section-$1'], $k) => $m[0],
        ])->all();
    @endphp
    <x-ui.page-header :title="$label">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.pages.index')">{{ __('dashboard.pages.title') }}</x-ui.button>
            @if ($url !== null)
                <x-ui.button variant="outline" :href="$url" icon-end="external-link" target="_blank" rel="noopener">{{ __('dashboard.view_on_site') }}</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @if ($errors->any())
        <x-ui.error-summary :errors="$summary" :title="__('dashboard.pages.errors.summary')" />
    @endif

    <form class="ui-editor" method="post" action="{{ route('dashboard.pages.update', $key) }}" data-page-editor>
        @csrf
        @method('PUT')

        <x-ui.fieldset :legend="__('dashboard.pages.status')" id="status">
            <div class="ui-editor__options">
                <x-ui.radio :label="__('dashboard.pages.status_draft')" name="status" value="draft" id="status-draft" :checked="$status === 'draft'" />
                <x-ui.radio :label="__('dashboard.pages.status_published')" name="status" value="published" id="status-published" :checked="$status === 'published'" />
            </div>
        </x-ui.fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.pages.page_text') }}</legend>
            <div class="ui-bilingual">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    <div class="ui-bilingual__column">
                        <p class="ui-bilingual__language">{{ __('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english')) }}</p>
                        <x-ui.field :label="__('dashboard.pages.fields.title')" :for="'title_'.$locale" :hint="__('dashboard.pages.fields.title_hint')" :error="$errors->first('title_'.$locale)">
                            <x-ui.textarea :id="'title_'.$locale" :name="'title_'.$locale" :value="$field('title_'.$locale)" rows="2" maxlength="255" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                        <x-ui.field :label="__('dashboard.pages.fields.name')" :for="'name_'.$locale" :hint="__('dashboard.pages.fields.name_hint')" :error="$errors->first('name_'.$locale)">
                            <x-ui.input :id="'name_'.$locale" :name="'name_'.$locale" :value="$field('name_'.$locale)" maxlength="120" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                        <x-ui.field :label="__('dashboard.pages.fields.description')" :for="'description_'.$locale" :hint="__('dashboard.pages.fields.description_hint')" :error="$errors->first('description_'.$locale)">
                            <x-ui.textarea :id="'description_'.$locale" :name="'description_'.$locale" :value="$field('description_'.$locale)" rows="3" maxlength="300" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                    </div>
                @endforeach
            </div>
        </fieldset>

        <section class="ui-editor__group" aria-labelledby="sections-title">
            <h2 class="ui-editor__legend" id="sections-title">{{ __('dashboard.pages.sections') }}</h2>
            <p class="ui-note">{{ __('dashboard.pages.type_help') }}</p>
            @error('sections')
                <p class="ui-field__error"><x-ui.icon name="circle-alert" size="sm" /><span>{{ $message }}</span></p>
            @enderror
            <div class="ui-editor__sections" data-sections>
                @foreach ($cards as $position => $card)
                    @include('dashboard.pages._section', ['i' => $card['i'], 's' => $card['s'], 'n' => $position + 1])
                @endforeach
            </div>
            <template data-section-template>
                @include('dashboard.pages._section', ['i' => '__INDEX__', 's' => null, 'n' => '__N__'])
            </template>
            <p hidden data-add-section-row>
                <x-ui.button type="button" variant="outline" icon="file-text" data-add-section>{{ __('dashboard.pages.add_section') }}</x-ui.button>
            </p>
        </section>

        <div class="ui-editor__bar">
            <x-ui.button type="submit" size="lg">{{ __('dashboard.save') }}</x-ui.button>
        </div>
    </form>
@endsection
