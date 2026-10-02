{{--
    The partnership application (FR — docs/franchise/03 field matrix, fields 1–11; M47): five short groups in one reading
    column. The interest type is one of the Owner's six options, one per line (PF-06); "other" opens a description (pure
    CSS :has — no JavaScript needed; the server drops it for any other choice). Two separate required boxes, never
    pre-checked: the non-binding acknowledgement (PF-02) and the data-processing consent (PF-03) — each label is the
    approved text itself. No investment question, no upload, no marketing opt-in. Attribution travels in hidden fields:
    UTM values, the landing path and the referrer's domain only — never an IP or a full URL.
--}}
@php
    $field = fn (string $key): string => (string) __('franchise.fields.'.$key);
    $o = fn (string $key): array => (array) __('franchise.options.'.$key);
    $statement = fn ($version): string => implode("\n\n", \App\Services\Content\Pages::paragraphs((string) (app()->getLocale() === 'ar' ? $version?->text_ar : $version?->text_en)));
    $max = fn (string $key): int => (int) config('franchise.limits.'.$key);
@endphp
<section id="apply" class="ui-franchise__apply" aria-labelledby="apply-title">
    <h2 class="ui-franchise__heading" id="apply-title">{{ __('franchise.form_title') }}</h2>
    <p class="ui-note">{{ __('franchise.form_note') }}</p>

    @if ($errors->any())
        @php
            $summary = collect($errors->getMessages())->mapWithKeys(fn (array $messages, string $key): array => [
                $key => ($key === 'form' ? '' : __('franchise.fields.'.$key).': ').$messages[0],
            ])->all();
        @endphp
        <x-ui.error-summary :errors="$summary" :title="__('franchise.errors.summary')" class="ui-apply__summary" />
    @endif

    <form class="ui-apply__form" method="post" action="{{ \App\Support\PageUrl::route('franchise.submit') }}" novalidate
        data-franchise-form data-label-submitting="{{ __('franchise.submitting') }}">
        @csrf
        <input type="hidden" name="form_token" value="{{ $formToken }}">
        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
        @foreach ($attribution as $key => $value)
            <input type="hidden" name="attr_{{ $key }}" value="{{ old('attr_'.$key, $value) }}">
        @endforeach
        <div class="ui-apply__hp" aria-hidden="true">
            <label for="{{ \App\Services\Forms\FormGuard::HONEYPOT }}">Leave this field empty</label>
            <input id="{{ \App\Services\Forms\FormGuard::HONEYPOT }}" name="{{ \App\Services\Forms\FormGuard::HONEYPOT }}" type="text" tabindex="-1" autocomplete="off">
        </div>

        <fieldset class="ui-apply__group">
            <legend class="ui-apply__legend">{{ __('franchise.groups.about') }}</legend>
            <x-ui.field :label="$field('full_name')" for="full_name" :error="$errors->first('full_name')" required>
                <x-ui.input id="full_name" name="full_name" :value="old('full_name')" autocomplete="name" dir="auto" :maxlength="$max('full_name')" />
            </x-ui.field>
            <x-ui.field :label="$field('phone')" for="phone" :error="$errors->first('phone')" required>
                <x-ui.input id="phone" name="phone" type="tel" :value="old('phone')" autocomplete="tel" inputmode="tel" dir="ltr" :maxlength="$max('phone')" />
            </x-ui.field>
            <x-ui.field :label="$field('email')" for="email" :error="$errors->first('email')" required>
                <x-ui.input id="email" name="email" type="email" :value="old('email')" autocomplete="email" dir="ltr" :maxlength="$max('email')" />
            </x-ui.field>
        </fieldset>

        <fieldset class="ui-apply__group">
            <legend class="ui-apply__legend">{{ __('franchise.groups.market') }}</legend>
            <x-ui.field :label="$field('country')" for="country" :error="$errors->first('country')" required>
                <x-ui.select id="country" name="country" :options="$countries" :selected="old('country')" :placeholder="__('franchise.choose')" autocomplete="country" />
            </x-ui.field>
            <x-ui.field :label="$field('city')" for="city" :error="$errors->first('city')" required>
                <x-ui.input id="city" name="city" :value="old('city')" autocomplete="address-level2" dir="auto" :maxlength="$max('city')" />
            </x-ui.field>
            <x-ui.field :label="$field('market')" for="market" :error="$errors->first('market')" required>
                <x-ui.input id="market" name="market" :value="old('market')" dir="auto" :maxlength="$max('market')" />
            </x-ui.field>
        </fieldset>

        <fieldset class="ui-apply__group">
            <legend class="ui-apply__legend">{{ __('franchise.groups.experience') }}</legend>
            <x-ui.field :label="$field('experience_band')" for="experience_band" :error="$errors->first('experience_band')" required>
                <x-ui.select id="experience_band" name="experience_band" :options="$o('experience_band')" :selected="old('experience_band')" :placeholder="__('franchise.choose')" />
            </x-ui.field>
            <x-ui.field :label="$field('experience_text')" for="experience_text" :error="$errors->first('experience_text')">
                <x-ui.textarea id="experience_text" name="experience_text" :value="old('experience_text')" rows="3" dir="auto" :maxlength="$max('experience_text')" />
            </x-ui.field>
            @include('site.partials.choice', ['name' => 'owns_business', 'legend' => $field('owns_business'), 'options' => $o('yes_no')])
        </fieldset>

        <fieldset class="ui-apply__group">
            <legend class="ui-apply__legend">{{ __('franchise.groups.opportunity') }}</legend>
            @include('site.partials.choice', ['name' => 'partnership_interest_type', 'legend' => $field('partnership_interest_type'), 'options' => $o('partnership_interest_type'), 'stack' => true])
            <div class="ui-apply__when" data-when="interest_other">
                <x-ui.field :label="$field('partnership_interest_other')" for="partnership_interest_other" :error="$errors->first('partnership_interest_other')" required>
                    <x-ui.textarea id="partnership_interest_other" name="partnership_interest_other" :value="old('partnership_interest_other')" rows="3" dir="auto"
                        :maxlength="$max('partnership_interest_other')" data-franchise-other />
                </x-ui.field>
            </div>
            @include('site.partials.choice', ['name' => 'location_status', 'legend' => $field('location_status'), 'options' => $o('location_status')])
            <x-ui.field :label="$field('introduction')" for="introduction" :error="$errors->first('introduction')" required>
                <x-ui.textarea id="introduction" name="introduction" :value="old('introduction')" rows="5" dir="auto" :maxlength="$max('introduction')" />
            </x-ui.field>
        </fieldset>

        <fieldset class="ui-apply__group">
            <legend class="ui-apply__legend">{{ __('franchise.groups.consent') }}</legend>
            <x-ui.checkbox :label="$statement($acknowledgement)" name="non_binding_acknowledgement" value="1" id="non_binding_acknowledgement"
                :checked="old('non_binding_acknowledgement') === '1'" :error="$errors->first('non_binding_acknowledgement')" />
            <x-ui.checkbox :label="$statement($consent)" name="data_processing_consent" value="1" id="data_processing_consent"
                :checked="old('data_processing_consent') === '1'" :error="$errors->first('data_processing_consent')" />
        </fieldset>

        @error('form')
            <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
        @enderror
        <div class="ui-apply__actions">
            <x-ui.button type="submit" size="lg" data-franchise-submit>{{ __('franchise.submit') }}</x-ui.button>
        </div>
    </form>
</section>
