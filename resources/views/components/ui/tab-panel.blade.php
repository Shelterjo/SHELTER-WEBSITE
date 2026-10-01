{{-- Tab panel: child of x-ui.tabs (reads the tabs id and the selected key through @aware). --}}
@aware([
    'id' => null,
    'selected' => null,
])
@props([
    'tab',
])
<div {{ $attributes->except(['id', 'selected'])->class('ui-tabs__panel')->merge([
    'role' => 'tabpanel',
    'id' => $id.'-panel-'.$tab,
    'aria-labelledby' => $id.'-tab-'.$tab,
    'tabindex' => '0',
    'hidden' => (string) $tab !== (string) $selected,
]) }}>{{ $slot }}</div>
