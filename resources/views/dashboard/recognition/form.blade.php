@extends('layouts.dashboard')

@section('title', $item->exists ? ($item->title_ar ?? __('dashboard.recognition.untitled')) : __('dashboard.recognition.new_title'))

@section('content')
    {{--
        Add / change an Employee of the Month (RecognitionEditor): draft or published, the person (a SHELTER Family
        profile shown with their consent), the month, the title and a short text in both languages, the photo and where
        it shows. Then: why it is not on the site (if so), archive / bring back, and the last versions.
    --}}
    @php
        $R = 'dashboard.recognition.';
        $ar = app()->getLocale() === 'ar';
        $status = old('status', in_array($item->status, ['draft', null], true) ? 'draft' : 'published');
        $places = (array) old('placements', $item->placements ?? []);
        $title = $item->exists ? (($ar ? $item->title_ar : $item->title_en) ?? $item->title_ar ?? __($R.'untitled')) : __($R.'new_title');
        $badges = [
            'active' => ['success', 'circle-check'], 'not_shown' => ['warning', 'triangle-alert'], 'scheduled' => ['info', 'calendar'],
            'draft' => ['neutral', 'file-text'], 'expired' => ['neutral', 'clock'], 'archived' => ['neutral', 'inbox'],
        ];
    @endphp
    <x-ui.page-header :title="$title">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.recognition.index')">{{ __($R.'title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($item->exists)
        <p class="ui-record__status">
            <x-ui.badge :variant="$badges[$state][0]" :icon="$badges[$state][1]">{{ __($R.'states.'.$state) }}</x-ui.badge>
            <span>{{ __($R.'state_help.'.$state) }}</span>
        </p>
    @endif
    @if ($reasons !== [] && in_array($state, ['not_shown', 'scheduled'], true))
        <x-ui.alert variant="warning" :title="__($R.'reasons_title')">
            <ul class="ui-record__lines" role="list">
                @foreach ($reasons as $reason)
                    <li>{{ __($R.'reasons.'.$reason) }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.error-summary :errors="$errors" :title="__('dashboard.pages.errors.summary')" />
    @endif

    <form class="ui-editor" method="post" action="{{ $item->exists ? route('dashboard.recognition.update', $item) : route('dashboard.recognition.store') }}">
        @csrf
        @if ($item->exists)
            @method('PUT')
        @endif

        <x-ui.fieldset :legend="__('dashboard.events.fields.status')" id="status">
            <div class="ui-editor__options">
                @foreach (['draft', 'published'] as $option)
                    <x-ui.radio :label="__($R.'statuses.'.$option)" name="status" :value="$option" :id="'status-'.$option" :checked="$status === $option" />
                @endforeach
            </div>
        </x-ui.fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __($R.'groups.who') }}</legend>
            <p class="ui-note">{{ __($R.'consent_help') }} <a href="{{ route('dashboard.team.index') }}">{{ __('dashboard.team.title') }}</a></p>
            <x-ui.field :label="__($R.'fields.member')" for="team_member_id" :error="$errors->first('team_member_id')">
                <x-ui.select id="team_member_id" name="team_member_id" :options="$members" :selected="(string) old('team_member_id', $memberId)" :placeholder="__($R.'fields.choose')" />
            </x-ui.field>
            <x-ui.fieldset :legend="__($R.'fields.month')" id="month" :hint="__($R.'fields.month_hint')" :error="$errors->first('month')">
                <div class="ui-editor__pair">
                    <x-ui.field :label="__($R.'fields.month_number')" for="month_number">
                        <x-ui.select id="month_number" name="month" :options="$months" :selected="(string) old('month', $month?->format('n'))" :placeholder="__($R.'fields.choose')" />
                    </x-ui.field>
                    <x-ui.field :label="__($R.'fields.year')" for="year">
                        <x-ui.select id="year" name="year" :options="$years" :selected="(string) old('year', $month?->format('Y'))" :placeholder="__($R.'fields.choose')" />
                    </x-ui.field>
                </div>
            </x-ui.fieldset>
        </fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __('dashboard.events.groups.text') }}</legend>
            <div class="ui-bilingual">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    <div class="ui-bilingual__column">
                        <p class="ui-bilingual__language">{{ __('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english')) }}</p>
                        <x-ui.field :label="__($R.'fields.title')" :for="'title_'.$locale" :error="$errors->first('title_'.$locale)">
                            <x-ui.input :id="'title_'.$locale" :name="'title_'.$locale" :value="old('title_'.$locale, $item->{'title_'.$locale})" maxlength="120" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                        <x-ui.field :label="__($R.'fields.body')" :for="'body_'.$locale" :hint="__($R.'fields.body_hint')" :error="$errors->first('body_'.$locale)" optional>
                            <x-ui.textarea :id="'body_'.$locale" :name="'body_'.$locale" :value="old('body_'.$locale, $item->{'body_'.$locale})" rows="3" maxlength="300" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                    </div>
                @endforeach
            </div>
        </fieldset>

        <div class="ui-editor__group">
            @include('dashboard.media._picker', [
                'name' => 'media_id', 'id' => 'media_id', 'selected' => old('media_id', $item->media_id), 'images' => $images,
                'legend' => __($R.'fields.photo'), 'none' => __($R.'fields.profile_photo'), 'empty' => __('dashboard.team.fields.no_photos'),
            ])
        </div>

        <x-ui.fieldset :legend="__($R.'fields.placements')" id="placements" :hint="__($R.'fields.placements_hint')" :error="$errors->first('placements')">
            <div class="ui-editor__options">
                @foreach (\App\Services\Experiences\Recognitions::PLACEMENTS as $option)
                    <x-ui.checkbox :label="__($R.'placements.'.$option)" name="placements[]" :value="$option" :id="'placement-'.$option" :checked="in_array($option, $places, true)" />
                @endforeach
            </div>
        </x-ui.fieldset>

        <x-ui.field :label="__('dashboard.events.fields.reason')" for="reason" :hint="__('dashboard.events.fields.reason_hint')" optional>
            <x-ui.input id="reason" name="reason" maxlength="300" :value="old('reason')" />
        </x-ui.field>

        <div class="ui-editor__bar">
            <x-ui.button type="submit" size="lg">{{ __('dashboard.save') }}</x-ui.button>
        </div>
    </form>

    @if ($item->exists)
        <section class="ui-record__section ui-editor__aside" aria-labelledby="commands-title">
            <h2 id="commands-title" class="ui-record__title">{{ __('dashboard.events.commands_title') }}</h2>
            <div class="ui-record__archive">
                @php $command = $item->archived_at === null ? ['archive', 'inbox'] : ['restore', 'rotate-cw']; @endphp
                <form method="post" action="{{ route('dashboard.recognition.command', [$item, $command[0]]) }}">
                    @csrf
                    <x-ui.button type="submit" variant="outline" :icon="$command[1]">{{ __('dashboard.events.commands.'.$command[0]) }}</x-ui.button>
                </form>
            </div>
            <p class="ui-note">{{ __($R.'archive_help') }}</p>
        </section>

        @if ($versions->isNotEmpty())
            <section class="ui-record__section ui-editor__aside" aria-labelledby="versions-title">
                <h2 id="versions-title" class="ui-record__title">{{ __('dashboard.events.versions_title') }}</h2>
                <ul class="ui-record__lines ui-menu-item__lines" role="list">
                    @foreach ($versions as $version)
                        <li>
                            <bdi>#{{ $version->version }} · {{ $version->created_at?->setTimezone('Asia/Amman')->format('Y-m-d H:i') }}</bdi>
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
