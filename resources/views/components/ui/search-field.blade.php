{{--
    Search field with suggestions (DS §8 Search/Combobox, Menu IA spec §8): a visible label (never placeholder-only),
    a 16px search input with the combobox role (ARIA APG list autocomplete), a 44px clear button and an empty listbox
    that the page script fills with options. The script owns the interaction: Arrow keys move through options, Enter
    picks, Esc closes; this component only renders the accessible structure. No <form>: without JavaScript the field
    submits nothing (the browser's find-in-page still works on the server-rendered content).
--}}
@props([
    'id',
    'label',
    'placeholder' => null,
    'listLabel' => null,
])
<div {{ $attributes->class('ui-search')->merge(['role' => 'search']) }}>
    <label class="ui-field__label" for="{{ $id }}">{{ $label }}</label>
    <div class="ui-search__box">
        <x-ui.icon name="search" class="ui-search__icon" />
        <input {{ (new \Illuminate\View\ComponentAttributeBag)->merge(array_filter([
            'class' => 'ui-input ui-search__input',
            'id' => $id,
            'type' => 'search',
            'role' => 'combobox',
            'aria-autocomplete' => 'list',
            'aria-expanded' => 'false',
            'aria-controls' => $id.'-list',
            'autocomplete' => 'off',
            'autocapitalize' => 'off',
            'spellcheck' => 'false',
            'enterkeyhint' => 'search',
            'placeholder' => $placeholder,
        ], fn ($v) => $v !== null)) }}>
        <x-ui.button variant="ghost" icon="x" icon-only :label="__('ui.search.clear')" class="ui-search__clear" data-ui-search-clear hidden />
    </div>
    <ul class="ui-search__list" id="{{ $id }}-list" role="listbox" aria-label="{{ $listLabel ?? __('ui.search.suggestions') }}" hidden></ul>
</div>
