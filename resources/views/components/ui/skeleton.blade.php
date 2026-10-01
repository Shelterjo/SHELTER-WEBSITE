{{--
    Skeleton: loading placeholder (DS-019). Announces "loading" once through a status role; the shapes are decorative.
    `decorative` hides it completely (use it when a parent region already announces loading with aria-busy).
--}}
@props([
    'lines' => 3,
    'media' => false,
    'label' => null,
    'decorative' => false,
])
<div {{ $attributes->class('ui-skeleton')->merge($decorative ? ['aria-hidden' => 'true'] : ['role' => 'status']) }}>
    @unless ($decorative)
        <span class="ui-visually-hidden">{{ $label ?? __('ui.loading') }}</span>
    @endunless
    @if ($media)
        <span class="ui-skeleton__block" aria-hidden="true"></span>
    @endif
    @for ($line = 0; $line < max(1, (int) $lines); $line++)
        <span class="ui-skeleton__line" aria-hidden="true"></span>
    @endfor
</div>
