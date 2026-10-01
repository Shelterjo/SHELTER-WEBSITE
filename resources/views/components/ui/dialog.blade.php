{{--
    Dialog base for x-ui.modal, x-ui.drawer and x-ui.bottom-sheet: native <dialog> opened with showModal() (Invoker
    Commands, JS fallback in resources/js/ui/dialog.ts) → focus trap, Esc closes, the page behind is inert, focus
    returns to the trigger. Focus starts on the title. Close: the visible 44px button (works without JavaScript through
    method="dialog"), Esc, or a click on the backdrop (closedby="any").
--}}
@props([
    'id',
    'title',
    'variant' => 'modal',
])
@php
    $variants = ['modal' => 'ui-modal', 'drawer' => 'ui-drawer', 'drawer-start' => 'ui-drawer ui-drawer--start', 'sheet' => 'ui-sheet', 'sheet-adaptive' => 'ui-sheet ui-sheet--adaptive'];
    if (! array_key_exists($variant, $variants)) {
        throw new InvalidArgumentException("x-ui.dialog: unknown variant [{$variant}].");
    }
@endphp
<dialog {{ $attributes->class(['ui-dialog', $variants[$variant]])->merge([
    'id' => $id,
    'aria-labelledby' => $id.'-title',
    'closedby' => 'any',
    'data-ui-dialog' => true,
]) }}>
    @if (str_starts_with($variant, 'sheet'))
        <div class="ui-sheet__handle" aria-hidden="true"></div>
    @endif
    <div class="ui-dialog__header">
        <h2 class="ui-dialog__title" id="{{ $id }}-title" tabindex="-1" autofocus>{{ $title }}</h2>
        <form method="dialog" class="ui-dialog__close">
            <x-ui.button type="submit" variant="ghost" icon="x" icon-only :label="__('ui.close')" />
        </form>
    </div>
    <div class="ui-dialog__body">{{ $slot }}</div>
    @isset($footer)
        <div class="ui-dialog__footer">{{ $footer }}</div>
    @endisset
</dialog>
