@extends('layouts.site')

@section('title', $page->name)

@section('content')
    {{--
        SI-B12 (docs/franchise/02 §1): DISCOVER → UNDERSTAND → TRUST → QUALIFY → APPLY. Every section is the Owner's
        published content (V1, M47); approved section types render as editorial text, check lists, cards (the current
        SHELTER experiences — never "packages"), a numbered journey, FAQ and the closing call to act. The primary CTA
        appears in the hero, mid-page, at the close and as a compact bar on phones once the hero is passed (UX-F-03).
        No financial figure, no availability or territory claim, no Offer/Price/Rating data — ever.
    --}}
    @php
        // Keys = positions in the page: #s-N for sections, #q-N for answers (search links to them).
        $faq = array_filter($page->sections, fn ($s) => $s->type === 'faq');
        $closing = array_filter($page->sections, fn ($s) => $s->type === 'cta');
        $body = array_filter($page->sections, fn ($s) => ! in_array($s->type, ['faq', 'cta'], true));
        $mid = (int) ceil(count($body) / 2);
        $ctaHref = $open ? '#apply' : '#franchise-contact';
        $firstSection = array_key_first($body);
    @endphp
    <div class="ui-page ui-franchise" data-franchise-page>
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-franchise__hero" data-franchise-hero>
                <p class="ui-eyebrow"><span lang="en" dir="ltr">{{ __('franchise.eyebrow') }}</span></p>
                <h1 class="ui-franchise__title">
                    @foreach ($page->titleLines as $line)
                        <span class="ui-franchise__title-line">{{ $line }}</span>
                    @endforeach
                </h1>
                @if ($page->description !== null)
                    <p class="ui-page-intro__lead">{{ $page->description }}</p>
                @endif
                <div class="ui-franchise__actions">
                    <x-ui.button size="lg" :href="$ctaHref" icon-end="arrow-right">{{ __('franchise.cta') }}</x-ui.button>
                    @if ($firstSection !== null)
                        <x-ui.button size="lg" variant="outline" :href="'#s-'.($firstSection + 1)">{{ __('franchise.secondary_cta') }}</x-ui.button>
                    @endif
                </div>
            </header>

            <div class="ui-franchise__layout">
                <div class="ui-franchise__body">
                    @foreach ($body as $position => $section)
                        <section class="ui-franchise__section" id="s-{{ $position + 1 }}" data-ui-reveal>
                            @if ($section->heading !== null)
                                <h2 class="ui-franchise__heading">{{ $section->heading }}</h2>
                            @endif
                            @if ($section->type === 'steps')
                                <ol class="ui-steps" role="list">
                                    @foreach ($section->paragraphs as $item)
                                        <li class="ui-steps__item">{{ $item }}</li>
                                    @endforeach
                                </ol>
                            @elseif ($section->type === 'list')
                                <ul class="ui-franchise__list" role="list">
                                    @foreach ($section->paragraphs as $item)
                                        <li class="ui-franchise__list-item"><x-ui.icon name="check" size="sm" /><span>{{ $item }}</span></li>
                                    @endforeach
                                </ul>
                            @elseif ($section->type === 'cards')
                                <div class="ui-franchise__cards">
                                    @foreach ($section->paragraphs as $card)
                                        @php [$cardTitle, $cardText] = array_pad(preg_split('/\R/u', $card, 2) ?: [], 2, ''); @endphp
                                        <article class="ui-franchise__card">
                                            <h3 class="ui-franchise__card-title" dir="auto">{{ trim($cardTitle) }}</h3>
                                            @if (trim($cardText) !== '')
                                                <p class="ui-franchise__card-text">{{ trim($cardText) }}</p>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="ui-prose">
                                    @foreach ($section->paragraphs as $paragraph)
                                        <p>{{ $paragraph }}</p>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                        @if ($loop->iteration === $mid && count($body) > 1)
                            <div class="ui-franchise__band">
                                <x-ui.button :href="$ctaHref" icon-end="arrow-right">{{ __('franchise.cta') }}</x-ui.button>
                            </div>
                        @endif
                    @endforeach

                    @if ($faq !== [])
                        <section class="ui-franchise__section" aria-labelledby="franchise-faq">
                            <h2 class="ui-franchise__heading" id="franchise-faq">{{ __('franchise.faq_title') }}</h2>
                            <div class="ui-prose">
                                @foreach ($faq as $position => $item)
                                    <x-ui.disclosure :summary="(string) $item->heading" class="ui-prose__faq" :id="'q-'.($position + 1)">
                                        @foreach ($item->paragraphs as $paragraph)
                                            <p>{{ $paragraph }}</p>
                                        @endforeach
                                    </x-ui.disclosure>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                {{-- One aside: after the content on phones, a sticky side column from 1024px (the CTA + the inquiries number). --}}
                @if ($open || $inquiries !== null)
                    <aside class="ui-franchise__aside" id="franchise-contact" aria-label="{{ __('franchise.closed_title') }}">
                        @if ($open)
                            <x-ui.button href="#apply" icon-end="arrow-right" class="ui-franchise__aside-cta">{{ __('franchise.cta') }}</x-ui.button>
                        @endif
                        @if ($inquiries !== null)
                            <x-ui.contact-card icon="handshake" :title="__('franchise.closed_title')" :lead="__('franchise.closed_text')" :phone="$inquiries" />
                        @endif
                    </aside>
                @endif
            </div>

            @foreach ($closing as $position => $section)
                <section class="ui-franchise__closing" id="s-{{ $position + 1 }}" data-ui-reveal @if ($section->heading !== null) aria-labelledby="closing-{{ $position + 1 }}" @endif>
                    @if ($section->heading !== null)
                        <h2 class="ui-franchise__closing-title" id="closing-{{ $position + 1 }}">{{ $section->heading }}</h2>
                    @endif
                    @foreach ($section->paragraphs as $paragraph)
                        <p class="ui-franchise__closing-text">{{ $paragraph }}</p>
                    @endforeach
                    <p class="ui-franchise__closing-action"><x-ui.button size="lg" :href="$ctaHref" icon-end="arrow-right">{{ __('franchise.final_cta') }}</x-ui.button></p>
                </section>
            @endforeach

            @if ($open)
                @include('site.franchise.form')
            @endif
        </div>
        <x-ui.action-bar :label="__('franchise.cta')" class="ui-franchise__bar" data-franchise-bar>
            <x-ui.button :href="$ctaHref">{{ __('franchise.cta') }}</x-ui.button>
        </x-ui.action-bar>
    </div>
@endsection
