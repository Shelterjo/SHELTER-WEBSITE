@extends('layouts.site')

@section('title', $page->title.' — '.__('site.brand'))

@section('content')
    {{--
        SI-B12 (docs/franchise/02 §1): DISCOVER → UNDERSTAND → TRUST → QUALIFY → APPLY. Every section is the Owner's
        published content (PO-030); approved section types render as editorial text, lists, a numbered journey and FAQ.
        The primary CTA appears in the hero, mid-page and as a compact bar on phones once the hero is passed (UX-F-03).
        No financial figure, no availability claim, no Offer/Price/Rating data — ever.
    --}}
    @php
        $faq = array_filter($page->sections, fn ($s) => $s->type === 'faq'); // keys = positions → #q-N (search links)
        $body = array_values(array_filter($page->sections, fn ($s) => $s->type !== 'faq'));
        $mid = (int) ceil(count($body) / 2);
        $ctaHref = $open ? '#apply' : '#franchise-contact';
    @endphp
    <div class="ui-page ui-franchise" data-franchise-page>
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-franchise__hero" data-franchise-hero>
                <p class="ui-eyebrow"><span lang="en" dir="ltr">{{ __('franchise.eyebrow') }}</span></p>
                <h1 class="ui-franchise__title">{{ $page->title }}</h1>
                @if ($page->description !== null)
                    <p class="ui-page-intro__lead">{{ $page->description }}</p>
                @endif
                <p><x-ui.button size="lg" :href="$ctaHref" icon-end="arrow-right">{{ __('franchise.cta') }}</x-ui.button></p>
            </header>

            <div class="ui-franchise__layout">
                <div class="ui-franchise__body">
                    @foreach ($body as $section)
                        <section class="ui-franchise__section" data-ui-reveal>
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

            @if ($open)
                @include('site.franchise.form')
            @endif
        </div>
        <x-ui.action-bar :label="__('franchise.cta')" class="ui-franchise__bar" data-franchise-bar>
            <x-ui.button :href="$ctaHref">{{ __('franchise.cta') }}</x-ui.button>
        </x-ui.action-bar>
    </div>
@endsection
