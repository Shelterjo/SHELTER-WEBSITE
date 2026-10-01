{{--
    Action bar (D-061 / BRANCH-017): on phones the branch page keeps its actions at the bottom of the screen — sticky
    inside the page flow, so it never hides the end of the content, and above the home indicator (safe area). From
    600px it is a normal row. Slot = up to three x-ui.button links. `label` names the group.
--}}
@props([
    'label',
])
<div {{ $attributes->class('ui-action-bar')->merge(['role' => 'group', 'aria-label' => $label]) }}>
    <div class="ui-action-bar__inner">{{ $slot }}</div>
</div>
