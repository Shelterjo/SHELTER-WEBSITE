{{--
    Fieldset (DS-015): a group of controls answering one question (radio group, checkbox group, the three date selects).
    The legend is the group label; hint and error are linked to the group with aria-describedby.
--}}
@props([
    'legend',
    'hint' => null,
    'error' => null,
])
@php
    $groupId = $attributes->get('id');
    if ((filled($hint) || filled($error)) && ! $groupId) {
        throw new InvalidArgumentException('x-ui.fieldset: a hint or an error needs an id on the fieldset.');
    }
    $describedBy = implode(' ', array_filter([
        filled($hint) ? $groupId.'-hint' : null,
        filled($error) ? $groupId.'-error' : null,
    ])) ?: null;
@endphp
<fieldset {{ $attributes->class('ui-fieldset')->merge(array_filter(['aria-describedby' => $describedBy])) }}>
    <legend class="ui-fieldset__legend">{{ $legend }}</legend>
    @if (filled($hint))
        <p class="ui-field__hint" id="{{ $groupId }}-hint">{{ $hint }}</p>
    @endif
    @if (filled($error))
        <p class="ui-field__error" id="{{ $groupId }}-error">
            <x-ui.icon name="circle-alert" size="sm" />
            <span><span class="ui-visually-hidden">{{ __('ui.error_prefix') }}</span> {{ $error }}</span>
        </p>
    @endif
    {{ $slot }}
</fieldset>
