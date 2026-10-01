{{--
    Section heading (DS §8 Section: eyebrow · title · description · CTA): one heading pattern for every page section.
    The link sits at the inline end on wide screens and under the text on phones. `level` = heading level (2–4).
--}}
@props([
    'title',
    'id' => null,
    'eyebrow' => null,
    'lead' => null,
    'href' => null,
    'linkLabel' => null,
    'level' => 2,
])
@php
    $level = max(2, min(4, (int) $level));
@endphp
<div {{ $attributes->class('ui-section-heading') }}>
    <div class="ui-section-heading__text">
        @if (filled($eyebrow))
            <p class="ui-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h{{ $level }} class="ui-section-heading__title" @if ($id) id="{{ $id }}" @endif>{{ $title }}</h{{ $level }}>
        @if (filled($lead))
            <p class="ui-section-heading__lead">{{ $lead }}</p>
        @endif
    </div>
    @if ($href && filled($linkLabel))
        <x-ui.button variant="link" :href="$href" icon-end="arrow-right" class="ui-section-heading__link">{{ $linkLabel }}</x-ui.button>
    @endif
</div>
