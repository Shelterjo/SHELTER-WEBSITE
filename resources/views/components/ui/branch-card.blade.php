{{--
    Branch card (DS §8 Branch card, BRANCH-016): approved name (+ the approved name in the other language), live open
    state, what kind of branch and where (+ its approved location description), today's hours, call and WhatsApp (D-062 — icon-only links carry a descriptive name, CONTACT-016) and a
    link to the branch page that covers the whole card (one tab stop; the contact links sit above it).
    Variants: `row` — an editorial list row (home), `panel` — a framed panel with labelled buttons (locations).
    `branch` = App\Services\Site\BranchSummary. Nothing is rendered for a value that is not approved.
--}}
@props([
    'branch',
    'variant' => 'row',
    'level' => 3,
    'detailsLabel' => null,
])
@php
    if (! in_array($variant, ['row', 'panel'], true)) {
        throw new InvalidArgumentException("x-ui.branch-card: unknown variant [{$variant}].");
    }
    $level = max(2, min(4, (int) $level));
    // Plain variables keep component attributes free of "->" (the localization test strips tags by their brackets).
    $statusTimeline = $branch->status;
    $kindInCity = $branch->kindInCity();
    $phoneHref = $branch->phone?->href;
    $whatsappHref = $branch->whatsapp?->href;
    // Icon-only actions name the branch (FINAL-QA QA-034): two cards on one page must not share one label.
    $callLabel = __('ui.contact.call_label', ['name' => $branch->name]);
    $whatsappLabel = __('ui.contact.whatsapp_label', ['name' => $branch->name]);
    $todayText = $branch->today === null ? null : (count($branch->today) === 0
        ? __('ui.hours.closed')
        : collect($branch->today)->map(fn (array $i): string => $i['opens'].' – '.$i['closes'])->implode(' · '));
@endphp
<article {{ $attributes->class(['ui-branch', 'ui-branch--'.$variant])->merge(['data-track-branch' => $branch->branch->slug, 'data-track-placement' => $variant === 'row' ? 'home_card' : 'locations_card']) }}>
    <div class="ui-branch__head">
        <h{{ $level }} class="ui-branch__name"><a class="ui-stretched" href="{{ $branch->url }}">{{ $branch->name }}</a></h{{ $level }}>
        @if ($branch->altName !== null)
            <p class="ui-branch__alt"><span lang="{{ $branch->altLocale }}" dir="{{ $branch->altLocale === 'ar' ? 'rtl' : 'ltr' }}">{{ $branch->altName }}</span></p>
        @endif
    </div>
    <div class="ui-branch__info">
        @if ($kindInCity !== null || $branch->landmark !== null)
            <p class="ui-branch__place">
                <x-ui.icon name="map-pin" size="sm" />
                <span>{{ $kindInCity }}@if ($kindInCity !== null && $branch->landmark !== null) · @endif{{ $branch->landmark }}</span>
            </p>
        @endif
        @if ($branch->status !== null)
            <x-ui.open-status :timeline="$statusTimeline" />
        @endif
        @if ($todayText !== null)
            <p class="ui-branch__today">
                <x-ui.icon name="clock" size="sm" />
                <span><span class="ui-branch__today-label">{{ __('ui.hours.today') }}</span> {{ $todayText }}</span>
            </p>
        @endif
    </div>
    @if ($branch->phone !== null || $branch->whatsapp !== null || $variant === 'row')
        <div class="ui-branch__actions">
            @if ($variant === 'panel')
                @if ($branch->phone !== null)
                    <x-ui.button variant="secondary" icon="phone" :href="$phoneHref">{{ __('ui.contact.call') }}</x-ui.button>
                @endif
                @if ($branch->whatsapp !== null)
                    <x-ui.button variant="secondary" icon="message-circle" :href="$whatsappHref" rel="noopener" target="_blank">{{ __('ui.contact.whatsapp') }}</x-ui.button>
                @endif
            @else
                @if ($branch->phone !== null)
                    <x-ui.button variant="ghost" icon="phone" icon-only :label="$callLabel" :href="$phoneHref" />
                @endif
                @if ($branch->whatsapp !== null)
                    <x-ui.button variant="ghost" icon="message-circle" icon-only :label="$whatsappLabel" :href="$whatsappHref" rel="noopener" target="_blank" />
                @endif
                <span class="ui-branch__go" aria-hidden="true"><x-ui.icon name="arrow-right" /></span>
            @endif
        </div>
    @endif
    @if ($variant === 'panel' && filled($detailsLabel))
        <p class="ui-branch__more" aria-hidden="true"><span>{{ $detailsLabel }}</span><x-ui.icon name="arrow-right" size="sm" /></p>
    @endif
</article>
