{{--
    Search form (GLOBAL-SEARCH §4): a plain GET form to the results page, so it works without JavaScript. Visible label
    (never placeholder-only), a 16px search input (q, max 100 characters) and a submit button. Used on the results page,
    in the navigation drawer and on the 404 page. `compact` = the button shows only its icon (name kept for readers).
--}}
@props([
    'action',
    'id',
    'label',
    'value' => null,
    'submitLabel' => null,
    'compact' => false,
])
<form {{ $attributes->class(['ui-search-form', 'ui-search-form--compact' => $compact]) }} action="{{ $action }}" method="get" role="search">
    <label class="ui-field__label" for="{{ $id }}">{{ $label }}</label>
    <div class="ui-search-form__row">
        <div class="ui-search__box ui-search-form__box">
            <x-ui.icon name="search" class="ui-search__icon" />
            <input class="ui-input ui-search__input ui-search-form__input" id="{{ $id }}" name="q" type="search" value="{{ $value }}"
                maxlength="100" autocomplete="off" autocapitalize="off" spellcheck="false" enterkeyhint="search">
        </div>
        @if ($compact)
            <x-ui.button type="submit" variant="secondary" icon="arrow-right" icon-only :label="$submitLabel ?? __('site.search.submit')" />
        @else
            <x-ui.button type="submit">{{ $submitLabel ?? __('site.search.submit') }}</x-ui.button>
        @endif
    </div>
</form>
