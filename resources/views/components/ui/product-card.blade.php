{{--
    Product card (Menu IA spec §6, R-02, R-04): approved image (media slot, explicit width/height) · primary name ·
    secondary name in the other language · price · at most ONE badge · branch availability text.
    - The whole card is ONE button (opens the detail dialog: `opens`) or ONE link (`href`); its description is the
      secondary name, the price (full currency for screen readers) and the status.
    - No media slot = no image box (launch state, M-02). `reserve-media` keeps an empty square for mixed sections.
    - Long names wrap, never truncate. ALL CAPS is a display style: pass `label` (normal case) if a screen reader
      spells a name letter by letter (D-131).
    - Unavailable: the frame and image fade, text and price stay fully readable.
--}}
@props([
    'name',
    'nameLang' => null,
    'secondary' => null,
    'secondaryLang' => null,
    'price' => null,
    'currency' => 'JOD',
    'badge' => null,
    'status' => null,
    'unavailable' => false,
    'href' => null,
    'opens' => null,
    'label' => null,
    'level' => 3,
    'reserveMedia' => false,
])
@php
    $cardId = $attributes->get('id', 'product-'.substr(sha1($name.'|'.$secondary.'|'.$price), 0, 10));
    $hasMedia = isset($media) && $media->isNotEmpty();
    if ($hasMedia && str_contains((string) $media, '<img') && preg_match('/<img(?=[^>]*\swidth=)(?=[^>]*\sheight=)/', (string) $media) !== 1) {
        throw new InvalidArgumentException('x-ui.product-card: the image needs explicit width and height (no layout shift).');
    }
    $level = max(2, min(6, (int) $level));
    $describedBy = implode(' ', array_filter([
        filled($secondary) ? $cardId.'-secondary' : null,
        filled($badge) ? $cardId.'-badge' : null,
        filled($status) ? $cardId.'-status' : null,
        $price !== null ? $cardId.'-price' : null,
    ])) ?: null;
    // merge() escapes every value (CMS text such as the label or href is never trusted).
    $actionAttributes = (new \Illuminate\View\ComponentAttributeBag)->merge(array_filter([
        'class' => 'ui-product-card__action ui-stretched',
        'href' => $href,
        'type' => $href === null ? 'button' : null,
        'aria-label' => $label,
        'aria-describedby' => $describedBy,
        'aria-haspopup' => $href === null && $opens !== null ? 'dialog' : null,
        'commandfor' => $href === null ? $opens : null,
        'command' => $href === null && $opens !== null ? 'show-modal' : null,
        'data-ui-dialog-open' => $href === null ? $opens : null,
    ], fn ($value) => $value !== null));
@endphp
<article {{ $attributes->class([
    'ui-card',
    'ui-product-card',
    'ui-product-card--has-media' => $hasMedia || $reserveMedia,
    'ui-product-card--unavailable' => $unavailable,
])->merge(['id' => $cardId]) }}>
    @if ($hasMedia)
        <div class="ui-card__media">{{ $media }}</div>
    @elseif ($reserveMedia)
        <div class="ui-card__media ui-card__media--reserved" aria-hidden="true"></div>
    @endif
    <div class="ui-product-card__body">
        <h{{ $level }} class="ui-product-card__name">
            @if ($href !== null)
                <a {{ $actionAttributes }}><span @if ($nameLang) lang="{{ $nameLang }}" @endif>{{ $name }}</span></a>
            @elseif ($opens !== null)
                <button {{ $actionAttributes }}><span @if ($nameLang) lang="{{ $nameLang }}" @endif>{{ $name }}</span></button>
            @else
                <span @if ($nameLang) lang="{{ $nameLang }}" @endif>{{ $name }}</span>
            @endif
        </h{{ $level }}>
        @if (filled($secondary))
            <p class="ui-product-card__secondary" id="{{ $cardId }}-secondary" @if ($secondaryLang) lang="{{ $secondaryLang }}" @endif>{{ $secondary }}</p>
        @endif
        @if (filled($badge))
            <p class="ui-product-card__badge" id="{{ $cardId }}-badge"><x-ui.badge>{{ $badge }}</x-ui.badge></p>
        @endif
        @if (filled($status))
            <p class="ui-product-card__status" id="{{ $cardId }}-status">
                <x-ui.icon name="info" size="sm" />
                <span>{{ $status }}</span>
            </p>
        @endif
        @if ($price !== null)
            <p class="ui-product-card__price" id="{{ $cardId }}-price"><x-ui.price :fils="$price" :currency="$currency" /></p>
        @endif
    </div>
</article>
