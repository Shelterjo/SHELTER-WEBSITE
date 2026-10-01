{{--
    Drawer / side panel: full height, from the inline end (quick view — default) or the inline start (`side="start"`,
    navigation). Logical margins put it on the correct side in RTL and LTR (DS §9). See x-ui.dialog.
--}}
@props([
    'id',
    'title',
    'side' => 'end',
])
<x-ui.dialog :variant="$side === 'start' ? 'drawer-start' : 'drawer'" :id="$id" :title="$title" {{ $attributes }}>
    {{ $slot }}
    @isset($footer)
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endisset
</x-ui.dialog>
