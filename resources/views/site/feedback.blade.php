@extends('layouts.site')

@section('title', __('feedback.title').' — '.__('site.brand'))

@section('content')
    {{--
        SI-B16 Voice of Customer (VOICE-OF-CUSTOMER §2): branch + overall experience required, four optional ratings and
        an optional comment. No name, phone or email field; the comment carries a visible "no personal details" hint.
        A link may preset the branch (?branch=drive — the QR/branch-page entry, PO-063). Questions = the M32 §15 example,
        final wording pending (PO-063). Works without JavaScript.
    --}}
    @php
        $selectedBranch = old('branch', collect($branches)->contains(fn ($b) => $b->branch->slug === $preset) ? $preset : null);
        $entry = old('entry', $selectedBranch !== null && $selectedBranch === $preset ? 'branch_link' : 'direct');
        $label = fn (string $key): string => (string) __('feedback.fields.'.$key);
    @endphp
    <div class="ui-page">
        <div class="ui-container">
            <section class="ui-feedback" aria-labelledby="feedback-title">
                <header class="ui-page-intro">
                    <h1 class="ui-page-intro__title" id="feedback-title">{{ __('feedback.title') }}</h1>
                    <p class="ui-page-intro__lead">{{ __('feedback.lead') }}</p>
                </header>
                <p class="ui-note">{{ __('feedback.required_note') }}</p>

                @if ($errors->any())
                    @php
                        $summary = collect($errors->getMessages())->mapWithKeys(fn (array $messages, string $key): array => [
                            $key => ($key === 'form' ? '' : __('feedback.fields.'.$key).': ').$messages[0],
                        ])->all();
                    @endphp
                    <x-ui.error-summary :errors="$summary" :title="__('feedback.errors.summary')" class="ui-apply__summary" />
                @endif

                <form class="ui-apply__form" method="post" action="{{ \App\Support\PageUrl::route('feedback.submit') }}" novalidate>
                    @csrf
                    <input type="hidden" name="form_token" value="{{ $formToken }}">
                    <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
                    <input type="hidden" name="entry" value="{{ $entry }}">
                    <div class="ui-apply__hp" aria-hidden="true">
                        <label for="{{ \App\Services\Forms\FormGuard::HONEYPOT }}">Leave this field empty</label>
                        <input id="{{ \App\Services\Forms\FormGuard::HONEYPOT }}" name="{{ \App\Services\Forms\FormGuard::HONEYPOT }}" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    <x-ui.fieldset :legend="$label('branch')" id="branch" :error="$errors->first('branch')" required class="ui-apply__choice">
                        <div class="ui-apply__options">
                            @foreach ($branches as $summary)
                                <x-ui.radio :label="$summary->name" name="branch" :value="$summary->branch->slug" :id="'branch-'.$summary->branch->slug"
                                    :checked="$selectedBranch === $summary->branch->slug" />
                            @endforeach
                        </div>
                    </x-ui.fieldset>

                    <x-ui.rating :legend="$label('rating_overall')" name="rating_overall" :selected="old('rating_overall')" :hint="__('feedback.scale_hint')"
                        :error="$errors->first('rating_overall')" required />

                    <fieldset class="ui-apply__group">
                        <legend class="ui-apply__legend">{{ __('feedback.details') }}</legend>
                        @foreach (['rating_coffee', 'rating_service', 'rating_cleanliness', 'rating_speed'] as $question)
                            <x-ui.rating :legend="$label($question)" :name="$question" :selected="old($question)" :error="$errors->first($question)" />
                        @endforeach
                        <x-ui.field :label="$label('comment')" for="comment" :hint="__('feedback.comment_hint')" :error="$errors->first('comment')">
                            <x-ui.textarea id="comment" name="comment" :value="old('comment')" rows="4" dir="auto" :maxlength="(int) config('feedback.limits.comment')" />
                        </x-ui.field>
                    </fieldset>

                    @error('form')
                        <x-ui.alert variant="danger">{{ $message }}</x-ui.alert>
                    @enderror
                    <div class="ui-apply__actions">
                        <x-ui.button type="submit" size="lg">{{ __('feedback.submit') }}</x-ui.button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
