{{--
    Contact card (D-059 contact by intent, docs/phase-01-discovery/14 §4): the reason for getting in touch first, then
    its approved number as real, copyable text that is also the call link (tel: in international form, display per
    D-065 — `dir="ltr"` keeps the digits in order on Arabic pages). Optional WhatsApp button (D-063 wording), email,
    and a link to a related page. The slot holds extra content (the branch list on the General card).
    `phone` / `whatsapp` / `email` = App\Services\Site\ContactAction|null — nothing is rendered for a missing value.
--}}
@props([
    'title',
    'lead' => null,
    'icon' => 'phone',
    'level' => 2,
    'phone' => null,
    'whatsapp' => null,
    'email' => null,
    'href' => null,
    'linkLabel' => null,
    'wide' => false,
])
@php
    $level = max(2, min(4, (int) $level));
    // Plain variables keep component attributes free of "->" (the localization test strips tags by their brackets).
    $whatsappHref = $whatsapp?->href;
@endphp
<article {{ $attributes->class(['ui-contact-card', 'ui-contact-card--wide' => $wide]) }}>
    <div class="ui-contact-card__head">
        <span class="ui-contact-card__icon"><x-ui.icon :name="$icon" /></span>
        <div class="ui-contact-card__heading">
            <h{{ $level }} class="ui-contact-card__title">{{ $title }}</h{{ $level }}>
            @if (filled($lead))
                <p class="ui-contact-card__lead">{{ $lead }}</p>
            @endif
        </div>
    </div>
    @if ($phone !== null || $whatsapp !== null || $email !== null)
        <div class="ui-contact-card__actions">
            @if ($phone !== null)
                <a class="ui-contact-card__number" href="{{ $phone->href }}">
                    <x-ui.icon name="phone" />
                    <span><span class="ui-visually-hidden">{{ __('ui.contact.call') }}: </span><span dir="ltr">{{ $phone->display }}</span></span>
                </a>
            @endif
            @if ($whatsapp !== null)
                <x-ui.button variant="secondary" icon="message-circle" :href="$whatsappHref" rel="noopener" target="_blank">{{ __('site.branch.whatsapp') }}</x-ui.button>
            @endif
            @if ($email !== null)
                <a class="ui-contact-card__email" href="{{ $email->href }}"><x-ui.icon name="mail" /><span dir="ltr">{{ $email->display }}</span></a>
            @endif
        </div>
    @endif
    @if (! $slot->isEmpty())
        <div class="ui-contact-card__body">{{ $slot }}</div>
    @endif
    @if (filled($href) && filled($linkLabel))
        <a class="ui-contact-card__link" href="{{ $href }}"><span>{{ $linkLabel }}</span><x-ui.icon name="arrow-right" size="sm" /></a>
    @endif
</article>
