{{--
    Hero (DS §8 Sections — "every important hero is designed for its page", DS-029). Variant `brand`: typography
    only — no photo until media is approved (MEDIA PENDING OWNER APPROVAL) — the brand name set as display lines, a
    lead, the actions slot (one primary CTA) and a hairline mark echoing the emblem's hexagon (decorative).
    Motion (MOTION-014): the lines rise out of a mask with Motion (resources/js/ui/reveal.ts); CSS reveals them on its
    own if the script never arrives, and nothing moves with reduced motion or without scripting. `lines` = list of
    strings (the h1), `lang` = their language when it differs from the page.
--}}
@props([
    'lines',
    'eyebrow' => null,
    'eyebrowLang' => null,
    'lead' => null,
    'titleId' => 'hero-title',
    'lang' => null,
])
<section {{ $attributes->class(['ui-hero', 'ui-hero--split' => isset($aside)])->merge(['aria-labelledby' => $titleId, 'data-ui-hero' => true]) }}>
    <svg class="ui-hero__mark" viewBox="0 0 120 138" aria-hidden="true" focusable="false">
        <path pathLength="1" d="M60 1 119 35v68L60 137 1 103V35Z" fill="none" stroke="currentColor" stroke-width="1.75" vector-effect="non-scaling-stroke" />
        <path pathLength="1" d="M60 13 108 41v56L60 125 12 97V41Z" fill="none" stroke="currentColor" stroke-width="1.75" vector-effect="non-scaling-stroke" />
    </svg>
    <div class="ui-container ui-hero__layout">
    <div class="ui-hero__inner">
        @if (filled($eyebrow))
            <p class="ui-eyebrow ui-hero__eyebrow" data-ui-hero-item><span @if ($eyebrowLang) lang="{{ $eyebrowLang }}" dir="{{ $eyebrowLang === 'ar' ? 'rtl' : 'ltr' }}" @endif>{{ $eyebrow }}</span></p>
        @endif
        <h1 class="ui-hero__title" id="{{ $titleId }}" @if ($lang) lang="{{ $lang }}" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}" @endif>
            @foreach ($lines as $line)
                <span @class(['ui-hero__line', 'ui-hero__line--quiet' => ! $loop->first])><span class="ui-hero__line-text" data-ui-hero-line>{{ $line }}</span></span>
            @endforeach
        </h1>
        @if (filled($lead))
            <p class="ui-hero__lead" data-ui-hero-item>{{ $lead }}</p>
        @endif
        @if ($slot->isNotEmpty())
            <div class="ui-hero__actions" data-ui-hero-item>{{ $slot }}</div>
        @endif
    </div>
    @isset($aside)
        <div class="ui-hero__aside">{{ $aside }}</div>
    @endisset
    </div>
</section>
