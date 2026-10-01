{{--
    Tabs (ARIA APG, automatic activation): tablist + tabs + x-ui.tab-panel children. Keyboard (roving tabindex,
    Arrow keys follow the reading direction, Home/End) comes from resources/js/ui/tabs.ts. The server renders the full
    ARIA state, so the selected panel is correct before any JavaScript runs.
    `tabs` = [key => label]; `selected` is required (a key) so every panel knows its state.
--}}
@props([
    'id',
    'tabs',
    'selected',
    'label',
])
<div {{ $attributes->class('ui-tabs')->merge(['id' => $id, 'data-ui-tabs' => true]) }}>
    <div class="ui-tabs__list" role="tablist" aria-label="{{ $label }}">
        @foreach ($tabs as $key => $text)
            <button type="button" class="ui-tabs__tab" role="tab" id="{{ $id }}-tab-{{ $key }}"
                aria-controls="{{ $id }}-panel-{{ $key }}" aria-selected="{{ (string) $key === (string) $selected ? 'true' : 'false' }}"
                tabindex="{{ (string) $key === (string) $selected ? '0' : '-1' }}">{{ $text }}</button>
        @endforeach
    </div>
    {{ $slot }}
</div>
