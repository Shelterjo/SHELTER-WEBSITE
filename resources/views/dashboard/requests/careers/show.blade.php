@extends('layouts.dashboard')

@section('title', $application->reference_number)

@section('content')
    {{--
        One job application (CAREERS-065…070): status first (with what the applicant sees), then the application in a
        clear order — summary, person, residence, education, attachments (CV first), the applicant's notes, consent —
        then the Owner's own work: interview, internal notes, status history, earlier applications. The identity number
        is masked; showing it needs a re-confirmation and is recorded.
    --}}
    @php
        $job = $application->job;
        $ar = app()->getLocale() === 'ar';
        $date = fn ($d, string $format = 'Y-m-d') => $d?->timezone('Asia/Amman')->format($format);
        $yesNo = fn (bool $v): string => __('dashboard.requests.options.yes_no.'.($v ? '1' : '0'));
        $public = \App\Services\Recruitment\ApplicationTracker::PUBLIC[$application->status] ?? 'under_review';
        $badge = ['received' => 'info', 'under_review' => 'neutral', 'interview_shortlisted' => 'warning', 'interviewed' => 'neutral', 'accepted' => 'success', 'rejected' => 'danger', 'archived' => 'neutral'];
        $attachments = $application->attachments->sortBy(fn ($a) => $a->id === $job?->primary_attachment_id ? 0 : 1)->values();
        $current = $application->interviews->firstWhere('is_current', true);
        $city = $job?->city ? ($ar ? $job->city->name_ar : ($job->city->name_en ?? $job->city->name_ar)) : '';
    @endphp
    <x-ui.page-header :title="$job?->full_name ?? $application->reference_number" :description="$application->reference_number.' · '.$date($application->submitted_at, 'Y-m-d H:i')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.careers.index')">{{ __('dashboard.requests.careers_title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($errors->any())
        <x-ui.error-summary :errors="$errors" :title="__('dashboard.pages.errors.summary')" />
    @endif

    <div class="ui-record">
        <section class="ui-record__section ui-record__section--status" aria-labelledby="status-title">
            <h2 class="ui-record__title" id="status-title">{{ __('dashboard.requests.sections.status') }}</h2>
            <p class="ui-record__status">
                <x-ui.badge :variant="$badge[$application->status] ?? 'neutral'">{{ __('dashboard.requests.statuses.'.$application->status) }}</x-ui.badge>
                <span>{{ __('dashboard.requests.public_now', ['label' => __('dashboard.requests.public.'.$public)]) }}</span>
            </p>
            <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.status', $application) }}">
                @csrf
                <x-ui.field :label="__('dashboard.requests.status_new')" for="status" :error="$errors->first('status')">
                    <x-ui.select id="status" name="status" :options="collect($statuses)->mapWithKeys(fn ($s) => [$s => __('dashboard.requests.statuses.'.$s)])->all()" :selected="$application->status" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.status_note')" for="status-note">
                    <x-ui.textarea id="status-note" name="note" rows="2" maxlength="3000" />
                </x-ui.field>
                @if ($application->status === 'accepted')
                    <p class="ui-note">{{ __('dashboard.requests.archive_accepted_warning') }}</p>
                @endif
                <x-ui.button type="submit">{{ __('dashboard.requests.status_save') }}</x-ui.button>
            </form>
            @if ($application->status === 'archived')
                <div class="ui-record__archive">
                    <form method="post" action="{{ route('dashboard.careers.restore', $application) }}">
                        @csrf
                        <x-ui.button type="submit" variant="outline" icon="rotate-cw">{{ __('dashboard.requests.restore') }}</x-ui.button>
                    </form>
                    <x-ui.button variant="ghost" :href="route('dashboard.careers.delete', $application)" icon="circle-x">{{ __('dashboard.requests.delete') }}</x-ui.button>
                    <p class="ui-note">{{ __('dashboard.requests.delete_hint') }}</p>
                </div>
            @endif
        </section>

        <section class="ui-record__section" aria-labelledby="summary-title">
            <h2 class="ui-record__title" id="summary-title">{{ __('dashboard.requests.sections.summary') }}</h2>
            <dl class="ui-facts ui-facts--grid">
                <div><dt>{{ __('dashboard.requests.fields.job') }}</dt><dd>{{ $job?->job_title_text }}</dd></div>
                <div><dt>{{ __('dashboard.requests.fields.salary') }}</dt><dd><bdi>{{ $job ? __('dashboard.requests.salary', ['amount' => rtrim(rtrim((string) $job->expected_salary_jod, '0'), '.')]) : '' }}</bdi></dd></div>
                <div><dt>{{ __('dashboard.requests.fields.city') }}</dt><dd>{{ $city }}</dd></div>
                <div><dt>{{ __('dashboard.requests.fields.experience') }}</dt><dd>{{ $job ? __('dashboard.requests.options.experience_band.'.$job->experience_band) : '' }}</dd></div>
                <div><dt>{{ __('dashboard.requests.fields.reference') }}</dt><dd><bdi>{{ $application->reference_number }}</bdi></dd></div>
                <div><dt>{{ __('dashboard.requests.fields.viewed') }}</dt><dd><bdi>{{ $date($application->first_viewed_at, 'Y-m-d H:i') }}</bdi></dd></div>
            </dl>
        </section>

        @if ($job !== null)
            <section class="ui-record__section" aria-labelledby="personal-title">
                <h2 class="ui-record__title" id="personal-title">{{ __('dashboard.requests.sections.personal') }}</h2>
                <dl class="ui-facts ui-facts--grid">
                    <div><dt>{{ __('dashboard.requests.fields.name') }}</dt><dd>{{ $job->full_name }}</dd></div>
                    <div><dt>{{ __('dashboard.requests.fields.phone') }}</dt><dd><a href="tel:{{ $job->phone_normalized }}" dir="ltr">{{ $job->phone_raw }}</a></dd></div>
                    <div><dt>{{ __('dashboard.requests.fields.email') }}</dt><dd><a href="mailto:{{ $job->email }}" dir="ltr">{{ $job->email }}</a></dd></div>
                    <div><dt>{{ __('dashboard.requests.fields.gender') }}</dt><dd>{{ __('dashboard.requests.options.gender.'.$job->gender) }}</dd></div>
                    <div><dt>{{ __('dashboard.requests.fields.age') }}</dt><dd>{{ __('dashboard.requests.age_years', ['years' => (int) $job->birth_date->diffInYears(now('Asia/Amman'), true)]) }} · <bdi>{{ $job->birth_date->format('Y-m-d') }}</bdi></dd></div>
                    <div><dt>{{ __('dashboard.requests.fields.marital_status') }}</dt><dd>{{ __('dashboard.requests.options.marital_status.'.$job->marital_status) }}</dd></div>
                    <div><dt>{{ __('dashboard.requests.fields.nationality') }}</dt><dd>{{ __('dashboard.requests.options.nationality_type.'.$job->nationality_type) }}@if (filled($job->nationality_text)) — {{ $job->nationality_text }}@endif</dd></div>
                    @if ($masked !== null && $application->identity !== null)
                        <div>
                            <dt>{{ __('dashboard.requests.options.id_type.'.$application->identity->id_type) }}</dt>
                            <dd class="ui-record__identity">
                                <bdi dir="ltr">{{ $masked }}</bdi>
                                <x-ui.button size="sm" variant="ghost" :href="route('dashboard.careers.identity', $application)">{{ __('dashboard.requests.reveal') }}</x-ui.button>
                                <span class="ui-note">{{ __('dashboard.requests.reveal_hint') }}</span>
                            </dd>
                        </div>
                    @endif
                </dl>
            </section>

            <section class="ui-record__section" aria-labelledby="residence-title">
                <h2 class="ui-record__title" id="residence-title">{{ __('dashboard.requests.sections.residence') }} · {{ __('dashboard.requests.sections.qualification') }}</h2>
                <dl class="ui-facts ui-facts--grid">
                    <div><dt>{{ __('dashboard.requests.fields.city') }}</dt><dd>{{ $city }}</dd></div>
                    <div><dt>{{ __('dashboard.requests.fields.area') }}</dt><dd>{{ $job->area_text }}</dd></div>
                    <div><dt>{{ __('dashboard.requests.fields.education') }}</dt><dd>{{ __('dashboard.requests.options.education_level.'.$job->education_level) }}</dd></div>
                    <div><dt>{{ __('dashboard.requests.fields.experience') }}</dt><dd>{{ __('dashboard.requests.options.experience_band.'.$job->experience_band) }}</dd></div>
                    <div><dt>{{ __('dashboard.requests.fields.same_field') }}</dt><dd>{{ $yesNo($job->same_field_experience) }}</dd></div>
                    <div><dt>{{ __('dashboard.requests.fields.employed') }}</dt><dd>{{ $yesNo($job->currently_employed) }}</dd></div>
                    <div><dt>{{ __('dashboard.requests.fields.license') }}</dt><dd>{{ $yesNo($job->has_driving_license) }}</dd></div>
                </dl>
            </section>
        @endif

        <section class="ui-record__section" aria-labelledby="attachments-title">
            <h2 class="ui-record__title" id="attachments-title">{{ __('dashboard.requests.sections.attachments') }}</h2>
            @if ($attachments->isEmpty())
                <p class="ui-note">{{ __('dashboard.requests.no_attachments') }}</p>
            @else
                <ul class="ui-record__files" role="list">
                    @foreach ($attachments as $file)
                        <li class="ui-record__file">
                            <span class="ui-record__file-name">
                                <strong>{{ $file->id === $job?->primary_attachment_id ? __('dashboard.requests.cv') : __('dashboard.requests.attachment') }}</strong>
                                <bdi dir="ltr">{{ $file->original_filename }}</bdi>
                                <span class="ui-note"><bdi>{{ number_format($file->size_bytes / 1024, 0) }} KB</bdi></span>
                            </span>
                            <x-ui.button size="sm" variant="outline" :href="route('dashboard.requests.attachment', $file)" icon="arrow-right">{{ __('dashboard.requests.download') }}</x-ui.button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        @if (filled($job?->notes_text))
            <section class="ui-record__section" aria-labelledby="applicant-notes-title">
                <h2 class="ui-record__title" id="applicant-notes-title">{{ __('dashboard.requests.sections.applicant_notes') }}</h2>
                <p class="ui-record__text">{{ $job?->notes_text }}</p>
            </section>
        @endif

        <section class="ui-record__section" aria-labelledby="consent-title">
            <h2 class="ui-record__title" id="consent-title">{{ __('dashboard.requests.sections.consent') }}</h2>
            <ul class="ui-record__lines" role="list">
                @foreach ($consents as $consent)
                    <li><bdi>{{ __('dashboard.requests.consent_line', ['version' => $consent->version, 'date' => \Illuminate\Support\Carbon::parse($consent->accepted_at)->timezone('Asia/Amman')->format('Y-m-d H:i')]) }}</bdi></li>
                @endforeach
            </ul>
        </section>

        <section class="ui-record__section" id="interview" aria-labelledby="interview-title">
            <h2 class="ui-record__title" id="interview-title">{{ __('dashboard.requests.sections.interview') }}</h2>
            <p class="ui-note">{{ __('dashboard.requests.interview_hint') }}</p>
            @if ($current !== null)
                <p class="ui-record__status">
                    <strong>{{ __('dashboard.requests.interview_current') }}:</strong>
                    <bdi>{{ $current->interview_date->format('Y-m-d') }} · {{ substr($current->interview_time, 0, 5) }}</bdi>
                    · {{ $ar ? $current->location?->name_ar : $current->location?->name_en }}
                </p>
                @if (filled($current->internal_notes))
                    <p class="ui-record__text">{{ $current->internal_notes }}</p>
                @endif
            @else
                <p>{{ __('dashboard.requests.interview_none') }}</p>
            @endif
            <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.interview', $application) }}">
                @csrf
                <div class="ui-editor__pair">
                    <x-ui.field :label="__('dashboard.requests.interview_date')" for="date" :error="$errors->first('date')">
                        <x-ui.input type="date" id="date" name="date" :value="old('date')" />
                    </x-ui.field>
                    <x-ui.field :label="__('dashboard.requests.interview_time')" for="time">
                        <x-ui.input type="time" id="time" name="time" :value="old('time')" step="300" />
                    </x-ui.field>
                </div>
                <x-ui.field :label="__('dashboard.requests.interview_location')" for="location" :error="$errors->first('location')">
                    <x-ui.select id="location" name="location" :options="['' => __('ui.select_placeholder')] + $locations->mapWithKeys(fn ($l) => [$l->id => $ar ? $l->name_ar : $l->name_en])->all()" :selected="(string) old('location')" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.interview_notes')" for="interview-notes">
                    <x-ui.textarea id="interview-notes" name="notes" rows="2" maxlength="3000" :value="old('notes')" />
                </x-ui.field>
                <x-ui.button type="submit" icon="calendar">{{ __('dashboard.requests.interview_save') }}</x-ui.button>
            </form>
            @php $earlier = $application->interviews->where('is_current', false); @endphp
            @if ($earlier->isNotEmpty())
                <h3 class="ui-record__subtitle">{{ __('dashboard.requests.interview_previous') }}</h3>
                <ul class="ui-record__lines" role="list">
                    @foreach ($earlier as $old)
                        <li><bdi>{{ $old->interview_date->format('Y-m-d') }} · {{ substr($old->interview_time, 0, 5) }}</bdi> · {{ $ar ? $old->location?->name_ar : $old->location?->name_en }}</li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="ui-record__section" id="notes" aria-labelledby="notes-title">
            <h2 class="ui-record__title" id="notes-title">{{ __('dashboard.requests.sections.notes') }}</h2>
            <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.notes.store', $application) }}">
                @csrf
                <x-ui.field :label="__('dashboard.requests.notes_add')" for="note-body" :error="$errors->first('body')">
                    <x-ui.textarea id="note-body" name="body" rows="3" maxlength="3000" />
                </x-ui.field>
                <x-ui.button type="submit">{{ __('dashboard.requests.notes_save') }}</x-ui.button>
            </form>
            @if ($application->notes->isEmpty())
                <p class="ui-note">{{ __('dashboard.requests.notes_empty') }}</p>
            @else
                <ul class="ui-record__notes" role="list">
                    @foreach ($application->notes as $note)
                        <li class="ui-record__note">
                            <p class="ui-record__text">{{ $note->body }}</p>
                            <p class="ui-note">
                                <bdi>{{ __('dashboard.requests.notes_by', ['name' => $note->author?->name ?? '—', 'date' => $date($note->created_at, 'Y-m-d H:i')]) }}</bdi>
                                @if ($note->updated_at?->gt($note->created_at))
                                    {{ __('dashboard.requests.notes_edited') }}
                                @endif
                            </p>
                            <x-ui.disclosure :summary="__('dashboard.requests.notes_edit')">
                                <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.notes.update', [$application, $note]) }}">
                                    @csrf
                                    @method('PUT')
                                    <x-ui.field :label="__('dashboard.requests.notes_body')" :for="'note-'.$note->id">
                                        <x-ui.textarea :id="'note-'.$note->id" name="body" rows="3" maxlength="3000" :value="$note->body" />
                                    </x-ui.field>
                                    <x-ui.checkbox :label="__('dashboard.requests.notes_remove')" name="remove" value="1" :id="'note-remove-'.$note->id" />
                                    <x-ui.button type="submit" size="sm">{{ __('dashboard.requests.notes_save') }}</x-ui.button>
                                </form>
                            </x-ui.disclosure>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="ui-record__section" aria-labelledby="history-title">
            <h2 class="ui-record__title" id="history-title">{{ __('dashboard.requests.sections.history') }}</h2>
            <ul class="ui-record__lines" role="list">
                @foreach ($application->statusHistory as $change)
                    <li>
                        <strong>{{ __('dashboard.requests.history_line', ['from' => $change->old_status !== null ? __('dashboard.requests.statuses.'.$change->old_status) : '—', 'to' => __('dashboard.requests.statuses.'.$change->new_status)]) }}</strong>
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
                            @if ($other->type === 'JOB')
                                <a href="{{ route('dashboard.careers.show', $other->id) }}"><bdi>{{ $other->reference_number }}</bdi></a>
                            @else
                                <bdi>{{ $other->reference_number }}</bdi>
                            @endif
                            · <bdi>{{ $date($other->submitted_at) }}</bdi>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
