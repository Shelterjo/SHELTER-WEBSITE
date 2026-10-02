{{--
    Rating scale (DS-015 form family): one question answered 1…max (default 5) — a radio group in x-ui.fieldset, shown
    as a row of equal 44px numbered options. Native radios (keyboard arrows + screen readers for free); the chosen
    number is filled + bold (never colour alone). In Arabic the row reads right to left, so 1 sits at the start.
    Every value gets the same screen afterwards — the component never reacts to the score (no review gating).
--}}
@props([
    'legend',
    'name',
    'selected' => null,
    'max' => 5,
    'hint' => null,
    'error' => null,
    'required' => false,
])
@php
    $groupId = $attributes->get('id', $name);
    $max = max(2, min(10, (int) $max));
@endphp
<x-ui.fieldset :legend="$legend" :hint="$hint" :error="$error" :required="$required" {{ $attributes->class('ui-rating')->merge(['id' => $groupId]) }}>
    <div class="ui-rating__scale">
        @for ($value = 1; $value <= $max; $value++)
            <label class="ui-rating__option">
                <input class="ui-rating__input" type="radio" name="{{ $name }}" value="{{ $value }}" id="{{ $groupId }}-{{ $value }}"
                    @checked((string) $selected === (string) $value) @if (filled($error)) aria-invalid="true" @endif>
                <span class="ui-rating__value">{{ $value }}</span>
            </label>
        @endfor
    </div>
</x-ui.fieldset>
