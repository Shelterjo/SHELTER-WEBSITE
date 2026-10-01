{{--
    Bottom sheet: mobile detail view (Menu IA §7) — up to 86% of the viewport height, rounded top, safe-area insets.
    The handle is decorative: the close button, Esc and the backdrop always close it (WCAG 2.5.7). See x-ui.dialog.
--}}
@props([
    'id',
    'title',
])
<x-ui.dialog variant="sheet" :id="$id" :title="$title" {{ $attributes }}>
    {{ $slot }}
    @isset($footer)
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endisset
</x-ui.dialog>
