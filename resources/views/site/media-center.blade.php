@extends('layouts.site')

@section('title', $page->name)

@section('content')
    {{--
        SI-B13 Media Center + Press Kit (MEDIA-RIGHTS §5): the Owner's page text, then curated approved items only — key
        facts, press photos (MediaRights), verified awards. MISSING or PENDING items are simply absent (MR-T09).
    --}}
    <div class="ui-page">
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title">{{ $page->title }}</h1>
                @if ($page->description !== null)
                    <p class="ui-page-intro__lead">{{ $page->description }}</p>
                @endif
            </header>

            <div class="ui-press">
                @foreach ($page->sections as $section)
                    @if (in_array($section->type, ['text', 'list'], true))
                        <section class="ui-press__section">
                            @if ($section->heading !== null)
                                <h2 class="ui-press__heading">{{ $section->heading }}</h2>
                            @endif
                            <div class="ui-prose">
                                @foreach ($section->paragraphs as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach

                <section class="ui-press__section" aria-labelledby="press-facts">
                    <h2 class="ui-press__heading" id="press-facts">{{ __('press.facts_title') }}</h2>
                    <dl class="ui-press__facts">
                        @foreach ($facts as $fact)
                            <div class="ui-press__fact">
                                <dt class="ui-press__fact-label">{{ $fact['label'] }}</dt>
                                <dd class="ui-press__fact-value" @if ($fact['lang'] !== null) lang="{{ $fact['lang'] }}" dir="{{ $fact['lang'] === 'ar' ? 'rtl' : 'ltr' }}" @endif>{{ $fact['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                @if ($photos !== [])
                    <section class="ui-press__section" aria-labelledby="press-photos">
                        <h2 class="ui-press__heading" id="press-photos">{{ __('press.photos_title') }}</h2>
                        <div class="ui-press__photos">
                            @foreach ($photos as $photo)
                                <x-ui.picture :image="$photo" sizes="(min-width: 1024px) 30vw, (min-width: 600px) 45vw, 100vw" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($recognition !== null)
                    @include('site.partials.recognition', ['recognition' => $recognition])
                @endif

                @if ($highlights !== [])
                    <section class="ui-press__section" aria-labelledby="press-awards">
                        <h2 class="ui-press__heading" id="press-awards">{{ __('press.awards_title') }}</h2>
                        <div class="ui-award-grid">
                            @foreach ($highlights as $award)
                                @include('site.partials.award-card', ['award' => $award])
                            @endforeach
                        </div>
                        @if ($awardsUrl !== null)
                            <p><x-ui.button variant="outline" :href="$awardsUrl" icon-end="arrow-right">{{ __('press.all_awards') }}</x-ui.button></p>
                        @endif
                    </section>
                @endif
            </div>
        </div>
    </div>
@endsection
