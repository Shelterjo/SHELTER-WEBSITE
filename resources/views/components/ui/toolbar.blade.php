{{--
    Toolbar (dashboard): a labelled group of controls above a list or table (search, filters, bulk actions). It is a
    role="group", not an APG role="toolbar": the controls are mixed (inputs + buttons), so arrow keys stay with the
    inputs and every control keeps its own tab stop.
--}}
@props([
    'label',
])
<div {{ $attributes->class('ui-toolbar')->merge(['role' => 'group', 'aria-label' => $label]) }}>
    {{ $slot }}
</div>
