{{--
    Site footer (DS-018, CONTACT-015: not crowded): the logo, the same navigation as the header, ONE public number and
    WhatsApp (D-057, D-062, D-065) — only when approved — the language links and the copyright with the brand name.
    No address, social links or claims until each is approved (PO-010, D-025/D-036). `phone` / `whatsapp` =
    App\Services\Site\ContactAction|null; `contact` = URL of the contact page when it exists.
--}}
@props([
    'home',
    'nav' => [],
    'languages' => [],
    'phone' => null,
    'whatsapp' => null,
    'contact' => null,
])
<footer {{ $attributes->class('ui-site-footer') }}>
    <div class="ui-container">
        <div class="ui-site-footer__grid">
            <div class="ui-site-footer__brand">
                <a class="ui-site-footer__logo-link" href="{{ $home }}">
                    <img class="ui-site-footer__logo" src="/brand/logo-white-240.png" srcset="/brand/logo-white-480.png 2x" width="240" height="88"
                        alt="{{ __('site.brand') }}" lang="en" loading="lazy" decoding="async">
                </a>
            </div>
            @if (count($nav) > 0)
                <nav class="ui-site-footer__group" aria-labelledby="footer-explore">
                    <h2 class="ui-site-footer__title" id="footer-explore">{{ __('ui.footer.explore') }}</h2>
                    <ul class="ui-site-footer__list" role="list">
                        @foreach ($nav as $item)
                            <li><a class="ui-site-footer__link" href="{{ $item['href'] }}" @if ($item['current'] ?? null) aria-current="{{ $item['current'] }}" @endif>{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endif
            @if ($phone !== null || $whatsapp !== null || $contact !== null)
                <div class="ui-site-footer__group">
                    <h2 class="ui-site-footer__title">{{ __('ui.footer.contact') }}</h2>
                    <ul class="ui-site-footer__list" role="list">
                        @if ($phone !== null)
                            <li>
                                <a class="ui-site-footer__link" href="{{ $phone->href }}">
                                    <x-ui.icon name="phone" size="sm" />
                                    <span dir="ltr">{{ $phone->display }}</span>
                                </a>
                            </li>
                        @endif
                        @if ($whatsapp !== null)
                            <li>
                                <a class="ui-site-footer__link" href="{{ $whatsapp->href }}" rel="noopener" target="_blank">
                                    <x-ui.icon name="message-circle" size="sm" />
                                    <span>{{ __('ui.contact.whatsapp') }}</span>
                                </a>
                            </li>
                        @endif
                        @if ($contact !== null)
                            <li><a class="ui-site-footer__link" href="{{ $contact }}">{{ __('ui.footer.all_contact') }}</a></li>
                        @endif
                    </ul>
                </div>
            @endif
            @if (count($languages) > 1)
                <div class="ui-site-footer__group">
                    <h2 class="ui-site-footer__title">{{ __('ui.navigation.language') }}</h2>
                    <ul class="ui-site-footer__list" role="list">
                        @foreach ($languages as $language)
                            <li>
                                <a class="ui-site-footer__link" href="{{ $language['href'] }}" lang="{{ $language['locale'] }}" hreflang="{{ $language['locale'] }}"
                                    @if ($language['current']) aria-current="true" @endif>{{ $language['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <p class="ui-site-footer__legal"><span lang="en" dir="ltr">© {{ now()->year }} {{ __('site.brand') }}</span></p>
    </div>
</footer>
