@extends('layouts.site')

@section('title', __('careers.title').' — '.__('site.brand'))

@section('content')
    {{--
        SI-B11 (CAREERS-REQUIREMENTS §3–§4): one organised page in six groups (A/B decision — docs/careers/wireframes),
        all 19 fields required, conditional fields shown by the chosen nationality (pure CSS :has — works without
        JavaScript; the server checks only the relevant ones). Files upload one by one when JavaScript runs; without it
        they travel with the form. The intro text is MISSING — OWNER INPUT REQUIRED: functional wording only.
    --}}
    @php
        $field = fn (string $key): string => (string) __('careers.fields.'.$key);
        $o = fn (string $key): array => (array) __('careers.options.'.$key);
        $yesNo = $o('yes_no');
        $months = collect($o('months'))->mapWithKeys(fn (string $name, int $n): array => [$n => $name.' ('.$n.')'])->all();
        $days = array_combine(range(1, 31), range(1, 31));
        $yearOptions = array_combine($years, $years);
        $cityOptions = $cities->mapWithKeys(fn ($city): array => [$city->id => $city->name_ar])->all();
        $trackUrl = \App\Support\PageUrl::route('careers.track');
    @endphp
    <div class="ui-page">
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title">{{ __('careers.title') }}</h1>
                <p class="ui-page-intro__lead">{{ __('careers.lead') }}</p>
                <p class="ui-careers__track"><a href="{{ $trackUrl }}">{{ __('careers.track_link') }}</a></p>
            </header>

            @if (! $open)
                <x-ui.alert>{{ __('careers.closed') }}</x-ui.alert>
            @else
                <section id="apply" class="ui-careers" aria-labelledby="apply-title">
                    <h2 class="ui-careers__title" id="apply-title">{{ __('careers.form_title') }}</h2>
                    <p class="ui-note">{{ __('careers.required_note') }}</p>

                    @if ($errors->any())
                        {{-- Each line names its field ("الاسم الكامل: هذا الحقل مطلوب.") and links to it. --}}
                        @php
                            $summary = collect($errors->getMessages())->mapWithKeys(fn (array $messages, string $key): array => [
                                $key => ($key === 'form' ? '' : __('careers.fields.'.$key).': ').$messages[0],
                            ])->all();
                        @endphp
                        <x-ui.error-summary :errors="$summary" :title="__('careers.errors.summary')" class="ui-careers__summary" />
                    @endif

                    <form class="ui-careers__form" method="post" action="{{ \App\Support\PageUrl::route('careers.submit') }}" enctype="multipart/form-data" novalidate
                        data-careers-form data-upload-url="{{ \App\Support\PageUrl::route('careers.upload') }}"
                        data-label-cv="{{ __('careers.upload.cv_badge') }}" data-label-remove="{{ __('careers.upload.remove') }}"
                        data-label-retry="{{ __('careers.upload.retry') }}" data-label-network="{{ __('careers.upload.errors.network') }}"
                        data-label-submitting="{{ __('careers.submitting') }}">
                        @csrf
                        <template data-careers-icon="x"><x-ui.icon name="x" /></template>
                        <template data-careers-icon="file-text"><x-ui.icon name="file-text" size="sm" /></template>
                        <input type="hidden" name="form_token" value="{{ $formToken }}">
                        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
                        <div class="ui-careers__hp" aria-hidden="true">
                            <label for="website">Website</label>
                            <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                        </div>

                        <fieldset class="ui-careers__group">
                            <legend class="ui-careers__legend">{{ __('careers.groups.personal') }}</legend>
                            <x-ui.field :label="$field('full_name')" for="full_name" :error="$errors->first('full_name')" required>
                                <x-ui.input id="full_name" name="full_name" :value="old('full_name')" autocomplete="name" dir="auto" maxlength="150" />
                            </x-ui.field>
                            <x-ui.field :label="$field('phone')" for="phone" :error="$errors->first('phone')" required>
                                <x-ui.input id="phone" name="phone" type="tel" :value="old('phone')" autocomplete="tel" inputmode="tel" dir="ltr" maxlength="40" />
                            </x-ui.field>
                            <x-ui.field :label="$field('email')" for="email" :error="$errors->first('email')" required>
                                <x-ui.input id="email" name="email" type="email" :value="old('email')" autocomplete="email" dir="ltr" maxlength="254" />
                            </x-ui.field>
                            @include('site.careers.choice', ['name' => 'gender', 'legend' => $field('gender'), 'options' => $o('gender')])
                            <x-ui.fieldset :legend="$field('birth_date')" id="birth_date" :error="$errors->first('birth_date')" required>
                                <div class="ui-careers__date">
                                    <x-ui.field :label="$field('birth_day')" for="birth_day">
                                        <x-ui.select id="birth_day" name="birth_day" :options="$days" :selected="old('birth_day')" :placeholder="__('careers.choose')" autocomplete="bday-day" />
                                    </x-ui.field>
                                    <x-ui.field :label="$field('birth_month')" for="birth_month">
                                        <x-ui.select id="birth_month" name="birth_month" :options="$months" :selected="old('birth_month')" :placeholder="__('careers.choose')" autocomplete="bday-month" />
                                    </x-ui.field>
                                    <x-ui.field :label="$field('birth_year')" for="birth_year">
                                        <x-ui.select id="birth_year" name="birth_year" :options="$yearOptions" :selected="old('birth_year')" :placeholder="__('careers.choose')" autocomplete="bday-year" />
                                    </x-ui.field>
                                </div>
                            </x-ui.fieldset>
                            <x-ui.field :label="$field('marital_status')" for="marital_status" :error="$errors->first('marital_status')" required>
                                <x-ui.select id="marital_status" name="marital_status" :options="$o('marital_status')" :selected="old('marital_status')" :placeholder="__('careers.choose')" />
                            </x-ui.field>
                            @include('site.careers.choice', ['name' => 'nationality_type', 'legend' => $field('nationality_type'), 'options' => $o('nationality_type')])
                            <div class="ui-careers__when" data-when="jordanian">
                                <x-ui.field :label="$field('national_id')" for="national_id" :error="$errors->first('national_id')" required>
                                    <x-ui.input id="national_id" name="national_id" inputmode="numeric" dir="ltr" autocomplete="off" maxlength="30" />
                                </x-ui.field>
                            </div>
                            <div class="ui-careers__when" data-when="non_jordanian">
                                <x-ui.field :label="$field('nationality_text')" for="nationality_text" :error="$errors->first('nationality_text')" required>
                                    <x-ui.input id="nationality_text" name="nationality_text" :value="old('nationality_text')" dir="auto" maxlength="80" />
                                </x-ui.field>
                                <x-ui.field :label="$field('document_number')" for="document_number" :error="$errors->first('document_number')" required>
                                    <x-ui.input id="document_number" name="document_number" dir="ltr" autocomplete="off" maxlength="30" />
                                </x-ui.field>
                            </div>
                        </fieldset>

                        <fieldset class="ui-careers__group">
                            <legend class="ui-careers__legend">{{ __('careers.groups.residence') }}</legend>
                            <x-ui.field :label="$field('city_id')" for="city_id" :error="$errors->first('city_id')" required>
                                <x-ui.select id="city_id" name="city_id" :options="$cityOptions" :selected="old('city_id')" :placeholder="__('careers.choose')" />
                            </x-ui.field>
                            <x-ui.field :label="$field('area')" for="area" :error="$errors->first('area')" required>
                                <x-ui.input id="area" name="area" :value="old('area')" dir="auto" maxlength="120" />
                            </x-ui.field>
                        </fieldset>

                        <fieldset class="ui-careers__group">
                            <legend class="ui-careers__legend">{{ __('careers.groups.qualification') }}</legend>
                            <x-ui.field :label="$field('education_level')" for="education_level" :error="$errors->first('education_level')" required>
                                <x-ui.select id="education_level" name="education_level" :options="$o('education_level')" :selected="old('education_level')" :placeholder="__('careers.choose')" />
                            </x-ui.field>
                            <x-ui.field :label="$field('experience_band')" for="experience_band" :error="$errors->first('experience_band')" required>
                                <x-ui.select id="experience_band" name="experience_band" :options="$o('experience_band')" :selected="old('experience_band')" :placeholder="__('careers.choose')" />
                            </x-ui.field>
                            @include('site.careers.choice', ['name' => 'same_field_experience', 'legend' => $field('same_field_experience'), 'options' => $yesNo])
                            @include('site.careers.choice', ['name' => 'currently_employed', 'legend' => $field('currently_employed'), 'options' => $yesNo])
                        </fieldset>

                        <fieldset class="ui-careers__group">
                            <legend class="ui-careers__legend">{{ __('careers.groups.work') }}</legend>
                            <x-ui.field :label="$field('job_title')" for="job_title" :error="$errors->first('job_title')" required>
                                <x-ui.input id="job_title" name="job_title" :value="old('job_title')" dir="auto" maxlength="150" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" />
                            </x-ui.field>
                            <x-ui.field :label="$field('expected_salary')" for="expected_salary" :error="$errors->first('expected_salary')" required>
                                <div class="ui-careers__money">
                                    <x-ui.input id="expected_salary" name="expected_salary" :value="old('expected_salary')" inputmode="decimal" dir="ltr" maxlength="9" />
                                    <span class="ui-careers__suffix" aria-hidden="true">{{ __('careers.currency') }}</span>
                                </div>
                            </x-ui.field>
                            @include('site.careers.choice', ['name' => 'has_driving_license', 'legend' => $field('has_driving_license'), 'options' => $yesNo])
                            <x-ui.field :label="$field('notes')" for="notes" :error="$errors->first('notes')" required>
                                <x-ui.textarea id="notes" name="notes" :value="old('notes')" rows="5" dir="auto" maxlength="3000" />
                            </x-ui.field>
                        </fieldset>

                        <fieldset class="ui-careers__group">
                            <legend class="ui-careers__legend">{{ __('careers.groups.attachments') }}</legend>
                            @include('site.careers.upload')
                        </fieldset>

                        <fieldset class="ui-careers__group">
                            <legend class="ui-careers__legend">{{ __('careers.groups.consent') }}</legend>
                            @if ($consent !== null)
                                <p class="ui-careers__consent-text" id="consent-text">{{ $consent->text_ar }}</p>
                            @endif
                            <x-ui.checkbox :label="__('careers.consent_label')" name="consent" value="1" id="consent" :checked="old('consent') === '1'"
                                :error="$errors->first('consent')" aria-describedby="consent-text" />
                        </fieldset>

                        @error('form')
                            <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
                        @enderror
                        <div class="ui-careers__actions">
                            <x-ui.button type="submit" size="lg" data-careers-submit>{{ __('careers.submit') }}</x-ui.button>
                        </div>
                    </form>
                </section>
            @endif
        </div>
    </div>
@endsection
