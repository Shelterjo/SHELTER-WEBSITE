{{-- Modal: centred dialog, max 760px (Menu IA §7) — product detail on desktop (R-05), confirmations. See x-ui.dialog. --}}
@props([
    'id',
    'title',
])
<x-ui.dialog variant="modal" :id="$id" :title="$title" {{ $attributes }}>
    {{ $slot }}
    @isset($footer)
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endisset
</x-ui.dialog>
