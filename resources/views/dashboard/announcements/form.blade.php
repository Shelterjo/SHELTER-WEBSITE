@extends('layouts.dashboard')

@section('title', $item->exists ? ($item->title_ar ?? __('dashboard.announcements.untitled')) : __('dashboard.announcements.new_title'))

@section('content')
    {{--
        Add / change an announcement or campaign (AnnouncementEditor): the Owner's own name for it, the kind, one place
        (top bar or home block), the branches it is for, the text in both languages, a button, an image from the approved
        media library (CAMP-004), when, the priority. Then: how it looks (both languages), what else wants the same place
        at the same time (DX-021), the commands and the last versions.
    --}}
    @php
        $A = 'dashboard.announcements.';
        $ar = app()->getLocale() === 'ar';
        $status = old('status', in_array($item->status, ['draft', null], true) ? 'draft' : 'published');
        $kind = old('type', $item->type ?? 'announcement');
        $placement = old('placement', ($item->placements ?? [])[0] ?? 'top_bar');
        $urgent = (bool) old('urgent', ($item->details['urgent'] ?? false) === true);
        $selected = array_map('intval', (array) old('branches', $item->branch_ids ?? []));
        $title = $item->exists ? (($item->details['internal_name'] ?? null) ?? ($ar ? $item->title_ar : $item->title_en) ?? $item->title_ar ?? __($A.'untitled')) : __($A.'new_title');
        $badges = [
            'live' => ['success', 'circle-check'], 'outranked' => ['warning', 'triangle-alert'], 'upcoming' => ['info', 'calendar'],
            'draft' => ['neutral', 'file-text'], 'paused' => ['warning', 'pause'], 'incomplete' => ['warning', 'triangle-alert'],
            'ended' => ['neutral', 'clock'], 'cancelled' => ['neutral', 'ban'], 'archived' => ['neutral', 'inbox'],
        ];
    @endphp
    <x-ui.page-header :title="$title">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.announcements.index')">{{ __($A.'title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($item->exists)
        <p class="ui-record__status">
            <x-ui.badge :variant="$badges[$state][0]" :icon="$badges[$state][1]">{{ __($A.'states.'.$state) }}</x-ui.badge>
            <span>{{ __($A.'state_help.'.$state) }}</span>
        </p>
    @endif
    @if ($conflicts !== [])
        <x-ui.alert variant="warning" :title="__($A.'conflict.title')">
            <ul class="ui-record__lines" role="list">
                @foreach ($conflicts as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.error-summary :errors="$errors" :title="__('dashboard.pages.errors.summary')" />
    @endif

    <form class="ui-editor" method="post" action="{{ $item->exists ? route('dashboard.announcements.update', $item) : route('dashboard.announcements.store') }}">
        @csrf
        @if ($item->exists)
            @method('PUT')
        @endif

        <x-ui.field :label="__($A.'fields.internal_name')" for="internal_name" :hint="__($A.'fields.internal_name_hint')" :error="$errors->first('internal_name')" optional>
            <x-ui.input id="internal_name" name="internal_name" maxlength="120" :value="old('internal_name', $item->details['internal_name'] ?? null)" />
        </x-ui.field>

        <x-ui.fieldset :legend="__('dashboard.events.fields.status')" id="status">
            <div class="ui-editor__options">
                @foreach (['draft', 'published'] as $option)
                    <x-ui.radio :label="__($A.'statuses.'.$option)" name="status" :value="$option" :id="'status-'.$option" :checked="$status === $option" />
                @endforeach
            </div>
        </x-ui.fieldset>

        <x-ui.fieldset :legend="__($A.'fields.type')" id="type">
            <div class="ui-editor__options">
                @foreach (\App\Services\Dashboard\AnnouncementEditor::TYPES as $option)
                    <x-ui.radio :label="__($A.'types.'.$option)" name="type" :value="$option" :id="'type-'.$option" :checked="$kind === $option" :hint="__($A.'type_hints.'.$option)" />
                @endforeach
            </div>
        </x-ui.fieldset>
        <div class="ui-urgent-option">
            <x-ui.checkbox :label="__($A.'fields.urgent')" name="urgent" value="1" id="urgent" :hint="__($A.'fields.urgent_hint')" :checked="$urgent" />
        </div>

        <x-ui.fieldset :legend="__($A.'fields.placement')" id="placement" :error="$errors->first('placement')">
            <div class="ui-editor__options">
                @foreach (\App\Services\Experiences\Placements::ALL as $option)
                    <x-ui.radio :label="__($A.'placements.'.$option)" name="placement" :value="$option" :id="'placement-'.$option" :checked="$placement === $option" :hint="__($A.'placement_hints.'.$option)" />
                @endforeach
            </div>
        </x-ui.fieldset>

        <x-ui.fieldset :legend="__($A.'fields.branches')" id="branches" :hint="__($A.'fields.branches_hint')" :error="$errors->first('branches')">
            <div class="ui-editor__options">
                @foreach ($branches as $id => $name)
                    <x-ui.checkbox :label="$name" name="branches[]" :value="$id" :id="'branch-'.$id" :checked="in_array($id, $selected, true)" />
                @endforeach
            </div>
        </x-ui.fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.events.groups.text') }}</legend>
            <div class="ui-bilingual">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    <div class="ui-bilingual__column">
                        <p class="ui-bilingual__language">{{ __('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english')) }}</p>
                        <x-ui.field :label="__($A.'fields.title')" :for="'title_'.$locale" :hint="__($A.'fields.title_hint')" :error="$errors->first('title_'.$locale)">
                            <x-ui.input :id="'title_'.$locale" :name="'title_'.$locale" :value="old('title_'.$locale, $item->{'title_'.$locale})" maxlength="120" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                        <x-ui.field :label="__($A.'fields.body')" :for="'body_'.$locale" :hint="__($A.'fields.body_hint')" :error="$errors->first('body_'.$locale)" optional>
                            <x-ui.input :id="'body_'.$locale" :name="'body_'.$locale" :value="old('body_'.$locale, $item->{'body_'.$locale})" maxlength="200" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                        <x-ui.field :label="__('dashboard.events.fields.cta_label')" :for="'cta_label_'.$locale" :error="$errors->first('cta_label_'.$locale)" optional>
                            <x-ui.input :id="'cta_label_'.$locale" :name="'cta_label_'.$locale" :value="old('cta_label_'.$locale, $item->{'cta_label_'.$locale})" maxlength="40" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                    </div>
                @endforeach
            </div>
            <x-ui.field :label="__('dashboard.events.fields.cta_url')" for="cta_url" :hint="__('dashboard.events.fields.cta_url_hint')" :error="$errors->first('cta_url')" optional>
                <x-ui.input id="cta_url" name="cta_url" inputmode="url" dir="ltr" :value="old('cta_url', $item->cta_url)" maxlength="500" />
            </x-ui.field>
        </fieldset>

        {{-- CAMP-004: only what the media library approved for the website is offered (MEDIA-RIGHTS); nothing else can be chosen. --}}
        <p class="ui-note">{{ __($A.'fields.media_hint') }}</p>
        @include('dashboard.media._picker', [
            'name' => 'media_id', 'id' => 'media_id', 'selected' => old('media_id', $item->media_id), 'images' => $images,
            'legend' => __($A.'fields.media'), 'none' => __('dashboard.menu.fields.no_image'), 'empty' => __('dashboard.awards.fields.no_images'),
        ])

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.events.groups.when') }}</legend>
            <p class="ui-note">{{ __($A.'when_help', ['market' => $market->name()]) }}</p>
            @foreach (['starts' => $starts, 'ends' => $ends] as $prefix => $moment)
                <div class="ui-editor__pair">
                    <x-ui.field :label="__('dashboard.events.fields.'.$prefix.'_date')" :for="$prefix.'_date'" :error="$errors->first($prefix.'_date')">
                        <x-ui.input type="date" :id="$prefix.'_date'" :name="$prefix.'_date'" :value="old($prefix.'_date', $moment?->format('Y-m-d'))" />
                    </x-ui.field>
                    <x-ui.field :label="__('dashboard.events.fields.'.$prefix.'_time')" :for="$prefix.'_time'" :error="$errors->first($prefix.'_time')">
                        <x-ui.input type="time" :id="$prefix.'_time'" :name="$prefix.'_time'" :value="old($prefix.'_time', $moment?->format('H:i'))" />
                    </x-ui.field>
                </div>
            @endforeach
        </fieldset>

        <x-ui.field :label="__($A.'fields.level')" for="level" :hint="__($A.'fields.level_hint')">
            <x-ui.select id="level" name="level" :options="collect(array_keys(\App\Services\Dashboard\AnnouncementEditor::LEVELS))->mapWithKeys(fn ($l) => [$l => __($A.'levels.'.$l)])->all()" :selected="old('level', $level)" />
        </x-ui.field>
        <x-ui.field :label="__('dashboard.events.fields.reason')" for="reason" :hint="__('dashboard.events.fields.reason_hint')" optional>
            <x-ui.input id="reason" name="reason" maxlength="300" :value="old('reason')" />
        </x-ui.field>

        <div class="ui-editor__bar">
            <x-ui.button type="submit" size="lg">{{ __('dashboard.save') }}</x-ui.button>
        </div>
    </form>

    @if ($item->exists && filled($item->title_ar) && filled($item->title_en))
        <section class="ui-preview ui-editor__aside" aria-labelledby="preview-title">
            <h2 id="preview-title" class="ui-record__title">{{ __('dashboard.events.preview_title') }}</h2>
            <p class="ui-note">{{ __($A.'preview_help') }}</p>
            <div class="ui-preview__compare">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    @php
                        $url = $item->cta_url === null ? null : (preg_replace('#^/(ar|en)/#', '/'.$locale.'/', $item->cta_url) ?? $item->cta_url);
                    @endphp
                    <div class="ui-preview__frame" lang="{{ $locale }}" dir="{{ $dir }}">
                        @if ((($item->placements ?? [])[0] ?? 'top_bar') === 'top_bar')
                            <x-ui.announcement-bar :title="$item->{'title_'.$locale}" :text="$item->{'body_'.$locale}" :href="$url" :link="$item->{'cta_label_'.$locale}" :urgent="($item->details['urgent'] ?? false) === true" />
                        @else
                            {{-- The home block draws the approved image above its text on a phone (CAMP-004). --}}
                            <x-ui.picture :image="$previewImages[$locale]" ratio="landscape" sizes="50vw" />
                            <x-ui.section-heading :eyebrow="__('site.home.feature.'.$item->type, [], $locale)" :title="$item->{'title_'.$locale}" :lead="$item->{'body_'.$locale}" :href="$url" :link-label="$item->{'cta_label_'.$locale}" />
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($item->exists)
        <section class="ui-record__section ui-editor__aside" aria-labelledby="commands-title">
            <h2 id="commands-title" class="ui-record__title">{{ __('dashboard.events.commands_title') }}</h2>
            <div class="ui-record__archive">
                @php
                    $commands = match ($state) {
                        'live', 'outranked' => ['pause' => 'pause', 'end' => 'clock', 'cancel' => 'ban'],
                        'upcoming', 'incomplete' => ['pause' => 'pause', 'cancel' => 'ban'],
                        'paused' => ['resume' => 'play', 'cancel' => 'ban'],
                        default => [],
                    };
                    $commands += $item->archived_at === null ? ['archive' => 'inbox'] : ['restore' => 'rotate-cw'];
                @endphp
                @foreach ($commands as $command => $icon)
                    <form method="post" action="{{ route('dashboard.announcements.command', [$item, $command]) }}">
                        @csrf
                        <x-ui.button type="submit" variant="outline" :icon="$icon">{{ __('dashboard.events.commands.'.$command) }}</x-ui.button>
                    </form>
                @endforeach
            </div>
            <p class="ui-note">{{ __('dashboard.events.commands_help') }}</p>
        </section>

        @if ($versions->isNotEmpty())
            <section class="ui-record__section ui-editor__aside" aria-labelledby="versions-title">
                <h2 id="versions-title" class="ui-record__title">{{ __('dashboard.events.versions_title') }}</h2>
                <ul class="ui-record__lines ui-menu-item__lines" role="list">
                    @foreach ($versions as $version)
                        <li>
                            <bdi>#{{ $version->version }} · {{ $version->created_at?->setTimezone($timezone)->format('Y-m-d H:i') }}</bdi>
                            · {{ __('dashboard.events.version_status.'.$version->status) }}
                            @if (filled($version->reason))
                                · {{ str_starts_with((string) $version->reason, 'command: ') ? __('dashboard.events.commands.'.substr((string) $version->reason, 9)) : $version->reason }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    @endif
@endsection
