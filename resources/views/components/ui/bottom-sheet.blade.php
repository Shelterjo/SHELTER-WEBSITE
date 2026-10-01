{{--
    Bottom sheet: mobile detail view (Menu IA §7) — up to 86% of the viewport height, rounded top, safe-area insets.
    The handle is decorative: the close button, Esc and the backdrop always close it (WCAG 2.5.7). See x-ui.dialog.
    `adaptive`: the same dialog becomes a centred modal from 1024px (product detail: sheet on mobile, modal on desktop —
    Menu IA §7, F-13, R-05), so a page needs one dialog, not two.
--}}
@props([
    'id',
    'title',
    'adaptive' => false,
])
<x-ui.dialog :variant="$adaptive ? 'sheet-adaptive' : 'sheet'" :id="$id" :title="$title" {{ $attributes }}>
    {{ $slot }}
    @isset($footer)
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endisset
</x-ui.dialog>
