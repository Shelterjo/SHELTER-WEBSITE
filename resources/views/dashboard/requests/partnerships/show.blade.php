@extends('layouts.dashboard')

@section('title', $application->reference_number)

@section('content')
    {{--
        One partnership application (docs/franchise/04 §3): the stage first (a human decision — nothing moves on its own),
        then contact, the opportunity, experience, the introduction, consents with their versions, where they came
        from — and the Owner's meetings, notes, stage history and earlier applications.
    --}}
    @php
        $p = $application->partnership;
        $ar = app()->getLocale() === 'ar';
        $date = fn ($d, string $format = 'Y-m-d') => $d?->timezone('Asia/Amman')->format($format);
    @endphp
    <x-ui.page-header :title="$p?->full_name ?? $application->reference_number" :description="$application->reference_number.' · '.$date($application->submitted_at, 'Y-m-d H:i')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.partnerships.index')">{{ __('dashboard.requests.partnerships_title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($errors->any())
        <x-ui.error-summary :errors="$errors" :title="__('dashboard.pages.errors.summary')" />
    @endif

    <div class="ui-record">
        <section class="ui-record__section ui-record__section--status" aria-labelledby="stage-title">
            <h2 class="ui-record__title" id="stage-title">{{ __('dashboard.requests.fr.sections.stage') }}</h2>
            <p class="ui-record__status"><x-ui.badge>{{ $labels[$application->status] ?? $application->status }}</x-ui.badge></p>
            <p class="ui-note">{{ __('dashboard.requests.fr.human_only') }}</p>
            <form class="ui-record__form" method="post" action="{{ route('dashboard.partnerships.status', $application) }}">
                @csrf
                <x-ui.field :label="__('dashboard.requests.fr.stage_new')" for="status" :error="$errors->first('status')">
                    <x-ui.select id="status" name="status" :options="$labels" :selected="$application->status" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.status_note')" for="status-note">
                    <x-ui.textarea id="status-note" name="note" rows="2" maxlength="3000" />
                </x-ui.field>
                <x-ui.button type="submit">{{ __('dashboard.requests.fr.stage_save') }}</x-ui.button>
            </form>
            @if ($application->status === 'archived')
                <form method="post" action="{{ route('dashboard.partnerships.restore', $application) }}">
                    @csrf
                    <x-ui.button type="submit" variant="outline" icon="rotate-cw">{{ __('dashboard.requests.restore') }}</x-ui.button>
                </form>
            @endif
        </section>

        @if ($p !== null)
            <section class="ui-record__section" aria-labelledby="contact-title">
                <h2 class="ui-record__title" id="contact-title">{{ __('dashboard.requests.fr.sections.contact') }}</h2>
                <dl class="ui-facts ui-facts--grid">
                    <div><dt>{{ __('franchise.fields.full_name') }}</dt><dd>{{ $p->full_name }}</dd></div>
                    <div><dt>{{ __('franchise.fields.phone') }}</dt><dd><a href="tel:{{ $p->phone_normalized }}" dir="ltr">{{ $p->phone_raw }}</a></dd></div>
                    <div><dt>{{ __('franchise.fields.email') }}</dt><dd><a href="mailto:{{ $p->email }}" dir="ltr">{{ $p->email }}</a></dd></div>
                    <div><dt>{{ __('franchise.fields.country') }}</dt><dd>{{ $countries[$p->country_code] ?? $p->country_code }}</dd></div>
                    <div><dt>{{ __('franchise.fields.city') }}</dt><dd>{{ $p->city_text }}</dd></div>
                </dl>
            </section>

            <section class="ui-record__section" aria-labelledby="opportunity-title">
                <h2 class="ui-record__title" id="opportunity-title">{{ __('dashboard.requests.fr.sections.opportunity') }} · {{ __('dashboard.requests.fr.sections.experience') }}</h2>
                <dl class="ui-facts ui-facts--grid">
                    <div><dt>{{ __('franchise.fields.market') }}</dt><dd>{{ $p->market_interest }}</dd></div>
                    <div><dt>{{ __('franchise.fields.partnership_interest_type') }}</dt><dd>{{ __('franchise.options.partnership_interest_type.'.$p->partnership_interest_type) }}@if (filled($p->partnership_interest_other)) — {{ $p->partnership_interest_other }}@endif</dd></div>
                    <div><dt>{{ __('franchise.fields.location_status') }}</dt><dd>{{ __('franchise.options.location_status.'.$p->location_status) }}</dd></div>
                    <div><dt>{{ __('franchise.fields.experience_band') }}</dt><dd>{{ __('franchise.options.experience_band.'.$p->experience_band) }}</dd></div>
                    <div><dt>{{ __('franchise.fields.owns_business') }}</dt><dd>{{ __('franchise.options.yes_no.'.($p->owns_business ? 'yes' : 'no')) }}</dd></div>
                    @if (filled($p->experience_text))
                        <div><dt>{{ __('franchise.fields.experience_text') }}</dt><dd class="ui-record__text">{{ $p->experience_text }}</dd></div>
                    @endif
                </dl>
            </section>

            <section class="ui-record__section" aria-labelledby="intro-title">
                <h2 class="ui-record__title" id="intro-title">{{ __('dashboard.requests.fr.sections.introduction') }}</h2>
                <p class="ui-record__text">{{ $p->introduction }}</p>
            </section>

            <section class="ui-record__section" aria-labelledby="attribution-title">
                <h2 class="ui-record__title" id="attribution-title">{{ __('dashboard.requests.fr.sections.attribution') }}</h2>
                @php $source = array_filter(['source' => $p->utm_source, 'medium' => $p->utm_medium, 'campaign' => $p->utm_campaign, 'landing' => $p->landing_path, 'referrer' => $p->referrer_domain]); @endphp
                @if ($source === [])
                    <p class="ui-note">{{ __('dashboard.requests.fr.attribution.none') }}</p>
                @else
                    <dl class="ui-facts ui-facts--grid">
                        @foreach ($source as $key => $value)
                            <div><dt>{{ __('dashboard.requests.fr.attribution.'.$key) }}</dt><dd><bdi dir="ltr">{{ $value }}</bdi></dd></div>
                        @endforeach
                    </dl>
                @endif
            </section>
        @endif

        <section class="ui-record__section" aria-labelledby="consents-title">
            <h2 class="ui-record__title" id="consents-title">{{ __('dashboard.requests.fr.sections.consents') }}</h2>
            <ul class="ui-record__lines" role="list">
                @foreach ($consents as $consent)
                    <li>
                        <strong>{{ __('dashboard.requests.fr.consent_scopes.'.$consent->scope) }}</strong>
                        <span class="ui-note"><bdi>{{ $consent->version }} · {{ \Illuminate\Support\Carbon::parse($consent->accepted_at)->timezone('Asia/Amman')->format('Y-m-d H:i') }}</bdi></span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="ui-record__section" id="meetings" aria-labelledby="meetings-title">
            <h2 class="ui-record__title" id="meetings-title">{{ __('dashboard.requests.fr.sections.meetings') }}</h2>
            <p class="ui-note">{{ __('dashboard.requests.fr.meetings_hint') }}</p>
            @if ($application->meetings->isEmpty())
                <p>{{ __('dashboard.requests.fr.meetings_none') }}</p>
            @else
                <ul class="ui-record__notes" role="list">
                    @foreach ($application->meetings as $meeting)
                        <li class="ui-record__note">
                            <p class="ui-record__status">
                                <strong><bdi>{{ $date($meeting->meeting_at, 'Y-m-d H:i') }}</bdi></strong>
                                · {{ __('dashboard.requests.fr.channels.'.$meeting->channel) }}
                                @if (filled($meeting->place)) · <bdi>{{ $meeting->place }}</bdi>@endif
                                <x-ui.badge>{{ __('dashboard.requests.fr.states.'.$meeting->state) }}</x-ui.badge>
                            </p>
                            @if (filled($meeting->internal_notes))
                                <p class="ui-record__text">{{ $meeting->internal_notes }}</p>
                            @endif
                            <form class="ui-record__archive" method="post" action="{{ route('dashboard.partnerships.meetings.update', [$application, $meeting]) }}">
                                @csrf
                                @method('PUT')
                                <x-ui.field :label="__('dashboard.requests.fr.meeting_state')" :for="'meeting-state-'.$meeting->id">
                                    <x-ui.select :id="'meeting-state-'.$meeting->id" name="state" :options="__('dashboard.requests.fr.states')" :selected="$meeting->state" />
                                </x-ui.field>
                                <x-ui.button type="submit" size="sm" variant="outline">{{ __('dashboard.requests.fr.meeting_state_save') }}</x-ui.button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
            <form class="ui-record__form" method="post" action="{{ route('dashboard.partnerships.meetings.store', $application) }}">
                @csrf
                <div class="ui-editor__pair">
                    <x-ui.field :label="__('dashboard.requests.interview_date')" for="date" :error="$errors->first('date')">
                        <x-ui.input type="date" id="date" name="date" :value="old('date')" />
                    </x-ui.field>
                    <x-ui.field :label="__('dashboard.requests.interview_time')" for="time">
                        <x-ui.input type="time" id="time" name="time" :value="old('time')" step="300" />
                    </x-ui.field>
                </div>
                <x-ui.field :label="__('dashboard.requests.fr.meeting_channel')" for="channel" :error="$errors->first('channel')">
                    <x-ui.select id="channel" name="channel" :options="['' => __('ui.select_placeholder')] + __('dashboard.requests.fr.channels')" :selected="(string) old('channel')" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.fr.meeting_place')" for="place">
                    <x-ui.input id="place" name="place" maxlength="150" :value="old('place')" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.interview_notes')" for="meeting-notes">
                    <x-ui.textarea id="meeting-notes" name="notes" rows="2" maxlength="3000" :value="old('notes')" />
                </x-ui.field>
                <x-ui.button type="submit" icon="calendar">{{ __('dashboard.requests.fr.meeting_save') }}</x-ui.button>
            </form>
        </section>

        @include('dashboard.requests._notes', ['store' => route('dashboard.partnerships.notes.store', $application), 'update' => fn ($note) => route('dashboard.partnerships.notes.update', [$application, $note])])

        <section class="ui-record__section" aria-labelledby="history-title">
            <h2 class="ui-record__title" id="history-title">{{ __('dashboard.requests.sections.history') }}</h2>
            <ul class="ui-record__lines" role="list">
                @foreach ($application->statusHistory as $change)
                    <li>
                        <strong>{{ __('dashboard.requests.history_line', ['from' => $change->old_status !== null ? ($labels[$change->old_status] ?? $change->old_status) : '—', 'to' => $labels[$change->new_status] ?? $change->new_status]) }}</strong>
                        <span class="ui-note"><bdi>{{ __('dashboard.requests.history_by', ['name' => $change->actor?->name ?? '—', 'date' => $date($change->changed_at, 'Y-m-d H:i')]) }}</bdi></span>
                        @if (filled($change->internal_note))
                            <span class="ui-record__text">{{ $change->internal_note }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="ui-record__section" aria-labelledby="previous-title">
            <h2 class="ui-record__title" id="previous-title">{{ __('dashboard.requests.sections.previous') }}</h2>
            @if ($previous === [])
                <p class="ui-note">{{ __('dashboard.requests.previous_none') }}</p>
            @else
                <ul class="ui-record__lines" role="list">
                    @foreach ($previous as $other)
                        <li>
                            <a href="{{ $other->type === 'FR' ? route('dashboard.partnerships.show', $other->id) : route('dashboard.careers.show', $other->id) }}"><bdi>{{ $other->reference_number }}</bdi></a>
                            · <bdi>{{ $date($other->submitted_at) }}</bdi>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
