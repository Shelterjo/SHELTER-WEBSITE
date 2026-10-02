@extends('layouts.dashboard')

@section('title', $event->exists ? ($event->title_ar ?? __('dashboard.events.untitled')) : __('dashboard.events.new_title'))

@section('content')
    {{--
        Add / change an event (EventEditor): draft or published, the text in both languages, when (market time), where,
        an optional button, the terms. Then: how visitors will see it (both languages), the commands and the last versions.
    --}}
    @php
        $E = 'dashboard.events.';
        $ar = app()->getLocale() === 'ar';
        $status = old('status', in_array($event->status, ['draft', null], true) ? 'draft' : 'published');
        $title = $event->exists ? (($ar ? $event->title_ar : $event->title_en) ?? $event->title_ar ?? $event->title_en ?? __($E.'untitled')) : __($E.'new_title');
        $badges = [
            'live' => ['success', 'circle-check'], 'upcoming' => ['info', 'calendar'], 'draft' => ['neutral', 'file-text'],
            'paused' => ['warning', 'pause'], 'incomplete' => ['warning', 'triangle-alert'], 'ended' => ['neutral', 'clock'],
            'cancelled' => ['neutral', 'ban'], 'archived' => ['neutral', 'inbox'],
        ];
        $selected = array_map('intval', (array) old('branches', $event->branch_ids ?? []));
        $details = $event->details ?? [];
    @endphp
    <x-ui.page-header :title="$title">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.events.index')">{{ __($E.'title') }}</x-ui.button>
            @if ($publicUrl !== null)
                <x-ui.button variant="outline" :href="$publicUrl" icon="external-link" target="_blank" rel="noopener">{{ __($E.'open_on_site') }}</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @if ($event->exists)
        <p class="ui-record__status">
            <x-ui.badge :variant="$badges[$state][0]" :icon="$badges[$state][1]">{{ __($E.'states.'.$state) }}</x-ui.badge>
            <span>{{ __($E.'state_help.'.$state) }}</span>
        </p>
    @endif

    @if ($errors->any())
        <x-ui.error-summary :errors="$errors" :title="__('dashboard.pages.errors.summary')" />
    @endif

    <form class="ui-editor" method="post" action="{{ $event->exists ? route('dashboard.events.update', $event) : route('dashboard.events.store') }}">
        @csrf
        @if ($event->exists)
            @method('PUT')
        @endif

        <x-ui.fieldset :legend="__($E.'fields.status')" id="status">
            <div class="ui-editor__options">
                @foreach (['draft', 'published'] as $option)
                    <x-ui.radio :label="__($E.'statuses.'.$option)" name="status" :value="$option" :id="'status-'.$option" :checked="$status === $option" />
                @endforeach
            </div>
        </x-ui.fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __($E.'groups.text') }}</legend>
            <div class="ui-bilingual">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    <div class="ui-bilingual__column">
                        <p class="ui-bilingual__language">{{ __('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english')) }}</p>
                        <x-ui.field :label="__($E.'fields.title')" :for="'title_'.$locale" :error="$errors->first('title_'.$locale)">
                            <x-ui.input :id="'title_'.$locale" :name="'title_'.$locale" :value="old('title_'.$locale, $event->{'title_'.$locale})" maxlength="200" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                        <x-ui.field :label="__($E.'fields.body')" :for="'body_'.$locale" :hint="__($E.'fields.body_hint')" :error="$errors->first('body_'.$locale)">
                            <x-ui.textarea :id="'body_'.$locale" :name="'body_'.$locale" :value="old('body_'.$locale, $event->{'body_'.$locale})" rows="6" maxlength="3000" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                        <x-ui.field :label="__($E.'fields.terms')" :for="'terms_'.$locale" :error="$errors->first('terms_'.$locale)" optional>
                            <x-ui.textarea :id="'terms_'.$locale" :name="'terms_'.$locale" :value="old('terms_'.$locale, $event->{'terms_'.$locale})" rows="3" maxlength="2000" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                    </div>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __($E.'groups.when') }}</legend>
            <p class="ui-note">{{ __($E.'when_help', ['market' => $market->name()]) }}</p>
            @foreach (['starts' => $starts, 'ends' => $ends] as $prefix => $moment)
                <div class="ui-editor__pair">
                    <x-ui.field :label="__($E.'fields.'.$prefix.'_date')" :for="$prefix.'_date'" :error="$errors->first($prefix.'_date')">
                        <x-ui.input type="date" :id="$prefix.'_date'" :name="$prefix.'_date'" :value="old($prefix.'_date', $moment?->format('Y-m-d'))" />
                    </x-ui.field>
                    <x-ui.field :label="__($E.'fields.'.$prefix.'_time')" :for="$prefix.'_time'" :error="$errors->first($prefix.'_time')">
                        <x-ui.input type="time" :id="$prefix.'_time'" :name="$prefix.'_time'" :value="old($prefix.'_time', $moment?->format('H:i'))" />
                    </x-ui.field>
                </div>
            @endforeach
        </fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __($E.'groups.where') }}</legend>
            <x-ui.fieldset :legend="__($E.'fields.branches')" id="branches" :error="$errors->first('branches')">
                <div class="ui-editor__options">
                    @foreach ($branches as $id => $name)
                        <x-ui.checkbox :label="$name" name="branches[]" :value="$id" :id="'branch-'.$id" :checked="in_array($id, $selected, true)" />
                    @endforeach
                </div>
            </x-ui.fieldset>
            <p class="ui-note">{{ __($E.'fields.venue_hint') }}</p>
            <div class="ui-bilingual">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    <div class="ui-bilingual__column">
                        <x-ui.field :label="__($E.'fields.venue').' — '.__('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english'))" :for="'venue_'.$locale" :error="$errors->first('venue_'.$locale)" optional>
                            <x-ui.input :id="'venue_'.$locale" :name="'venue_'.$locale" :value="old('venue_'.$locale, $details['venue_'.$locale] ?? null)" maxlength="200" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                    </div>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __($E.'groups.button') }}</legend>
            <p class="ui-note">{{ __($E.'fields.button_hint') }}</p>
            <div class="ui-bilingual">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    <div class="ui-bilingual__column">
                        <x-ui.field :label="__($E.'fields.cta_label').' — '.__('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english'))" :for="'cta_label_'.$locale" :error="$errors->first('cta_label_'.$locale)" optional>
                            <x-ui.input :id="'cta_label_'.$locale" :name="'cta_label_'.$locale" :value="old('cta_label_'.$locale, $event->{'cta_label_'.$locale})" maxlength="60" :lang="$locale" :dir="$dir" />
                        </x-ui.field>
                    </div>
                @endforeach
            </div>
            <x-ui.field :label="__($E.'fields.cta_url')" for="cta_url" :hint="__($E.'fields.cta_url_hint')" :error="$errors->first('cta_url')" optional>
                <x-ui.input type="text" id="cta_url" name="cta_url" inputmode="url" dir="ltr" :value="old('cta_url', $event->cta_url)" maxlength="500" />
            </x-ui.field>
        </fieldset>

        <fieldset class="ui-editor__group">
            <legend class="ui-editor__legend">{{ __($E.'groups.address') }}</legend>
            @if ($fixed)
                <p class="ui-note">{{ __($E.'fields.slug_fixed') }}</p>
                <p><bdi dir="ltr">/{{ app()->getLocale() }}/{{ $market->code }}/events/{{ $event->slug }}/</bdi></p>
            @else
                <x-ui.field :label="__($E.'fields.slug')" for="slug" :hint="__($E.'fields.slug_hint')" :error="$errors->first('slug')" optional>
                    <x-ui.input id="slug" name="slug" dir="ltr" :value="old('slug', $event->slug)" maxlength="80" pattern="[a-z0-9]+(-[a-z0-9]+)*" />
                </x-ui.field>
            @endif
        </fieldset>

        <x-ui.field :label="__($E.'fields.reason')" for="reason" :hint="__($E.'fields.reason_hint')" optional>
            <x-ui.input id="reason" name="reason" maxlength="300" :value="old('reason')" />
        </x-ui.field>

        <div class="ui-editor__bar">
            <x-ui.button type="submit" size="lg">{{ __('dashboard.save') }}</x-ui.button>
        </div>
    </form>

    @if ($preview !== null)
        <section class="ui-preview ui-editor__aside" aria-labelledby="preview-title">
            <h2 id="preview-title" class="ui-record__title">{{ __($E.'preview_title') }}</h2>
            <p class="ui-note">{{ __($E.'preview_help') }}</p>
            <div class="ui-preview__compare">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    @php $view = $preview[$locale]; @endphp
                    <article class="ui-record__section" lang="{{ $locale }}" dir="{{ $dir }}">
                        <p class="ui-bilingual__language">{{ __('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english')) }}</p>
                        @if ($view === null)
                            <x-ui.alert variant="warning">{{ __($E.'preview_missing', [], $locale) }}</x-ui.alert>
                        @else
                            <h3 class="ui-record__subtitle">{{ $view->title }}</h3>
                            <dl class="ui-facts">
                                <div>
                                    <dt>{{ __('site.events.when', [], $locale) }}</dt>
                                    <dd>
                                        {{ $view->dateText }}
                                        @if ($view->timeText !== null)
                                            · <bdi>{{ $view->timeText }}</bdi>
                                        @else
                                            <br>{{ __('site.events.starts', ['when' => $view->startText], $locale) }}
                                            <br>{{ __('site.events.ends', ['when' => $view->endText], $locale) }}
                                        @endif
                                    </dd>
                                </div>
                                @if ($view->place !== null)
                                    <div>
                                        <dt>{{ __('site.events.where', [], $locale) }}</dt>
                                        <dd>{{ $view->place }}</dd>
                                    </div>
                                @endif
                                @if ($view->paragraphs !== [])
                                    <div>
                                        <dt>{{ __($E.'fields.body', [], $locale) }}</dt>
                                        <dd>{{ $view->paragraphs[0] }}@if (count($view->paragraphs) > 1) …@endif</dd>
                                    </div>
                                @endif
                                @if ($view->ctaLabel !== null)
                                    <div>
                                        <dt>{{ __($E.'groups.button', [], $locale) }}</dt>
                                        <dd>{{ $view->ctaLabel }} → <bdi dir="ltr">{{ $view->ctaUrl }}</bdi></dd>
                                    </div>
                                @endif
                            </dl>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($event->exists)
        <section class="ui-record__section ui-editor__aside" aria-labelledby="commands-title">
            <h2 id="commands-title" class="ui-record__title">{{ __($E.'commands_title') }}</h2>
            <div class="ui-record__archive">
                @php
                    $commands = match ($state) {
                        'live' => ['pause' => 'pause', 'end' => 'clock', 'cancel' => 'ban'],
                        'upcoming', 'incomplete' => ['pause' => 'pause', 'cancel' => 'ban'],
                        'paused' => ['resume' => 'play', 'cancel' => 'ban'],
                        default => [],
                    };
                    $commands += $event->archived_at === null ? ['archive' => 'inbox'] : ['restore' => 'rotate-cw'];
                @endphp
                @foreach ($commands as $command => $icon)
                    <form method="post" action="{{ route('dashboard.events.command', [$event, $command]) }}">
                        @csrf
                        <x-ui.button type="submit" variant="outline" :icon="$icon">{{ __($E.'commands.'.$command) }}</x-ui.button>
                    </form>
                @endforeach
            </div>
            <p class="ui-note">{{ __($E.'commands_help') }}</p>
        </section>

        @if ($versions->isNotEmpty())
            <section class="ui-record__section ui-editor__aside" aria-labelledby="versions-title">
                <h2 id="versions-title" class="ui-record__title">{{ __($E.'versions_title') }}</h2>
                <ul class="ui-record__lines" role="list">
                    @foreach ($versions as $version)
                        <li>
                            <bdi>#{{ $version->version }} · {{ $version->created_at?->setTimezone($timezone)->format('Y-m-d H:i') }}</bdi>
                            · {{ __($E.'version_status.'.$version->status) }}
                            @if (filled($version->reason))
                                · {{ str_starts_with((string) $version->reason, 'command: ') ? __($E.'commands.'.substr((string) $version->reason, 9)) : $version->reason }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    @endif
@endsection
