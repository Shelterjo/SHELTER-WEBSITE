{{--
    Branch card (DS §8 Branch card, BRANCH-016): approved name (+ the approved name in the other language), live open
    state, what kind of branch and where (+ its approved location description), today's hours, and the same labelled
    actions in both variants (UX-006 DR-08): Directions — from the branch's approved Maps link, only when there is one
    (DR-10, D-061 order) — then call and WhatsApp (D-062, D-063). Each action also names the branch for screen readers,
    so two cards on one page never share a label (FINAL-QA QA-034, copy audit F42). A link to the branch page covers the
    whole card (one tab stop; the actions sit above it) and `detailsLabel` says where it goes.
    Variants: `row` — an editorial list row (home), `panel` — a framed panel (locations).
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
    $placeLine = $branch->placeLine();
    $mapsHref = $branch->mapsUrl;
    $phoneHref = $branch->phone?->href;
    $whatsappHref = $branch->whatsapp?->href;
    $branchName = $branch->name;
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
        @if ($placeLine !== null)
            <p class="ui-branch__place">
                <x-ui.icon name="map-pin" size="sm" />
                <span>{{ $placeLine }}</span>
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
    @if ($mapsHref !== null || $phoneHref !== null || $whatsappHref !== null)
        <div class="ui-branch__actions">
            @if ($mapsHref !== null)
                <x-ui.button variant="secondary" icon="map-pin" :href="$mapsHref" rel="noopener" target="_blank">{{ __('site.branch.directions_short') }}<span class="ui-visually-hidden"> — {{ $branchName }}</span></x-ui.button>
            @endif
            @if ($phoneHref !== null)
                <x-ui.button variant="secondary" icon="phone" :href="$phoneHref">{{ __('ui.contact.call') }}<span class="ui-visually-hidden"> — {{ $branchName }}</span></x-ui.button>
            @endif
            @if ($whatsappHref !== null)
                <x-ui.button variant="secondary" icon="message-circle" :href="$whatsappHref" rel="noopener" target="_blank">{{ __('ui.contact.whatsapp') }}<span class="ui-visually-hidden"> — {{ $branchName }}</span></x-ui.button>
            @endif
        </div>
    @endif
    @if (filled($detailsLabel))
        <p class="ui-branch__more" aria-hidden="true"><span>{{ $detailsLabel }}</span><x-ui.icon name="arrow-right" size="sm" /></p>
    @endif
</article>
