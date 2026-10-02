@extends('layouts.site')

@section('title', $page->title.' — '.__('site.brand'))

@section('content')
    {{-- SI-B03 / B05 / B06 / B07: published Owner text only (App\Services\Content\Pages); plain text, escaped. --}}
    @php($locale = app()->getLocale())
    <div class="ui-page">
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title">{{ $page->title }}</h1>
                @if ($page->description !== null)
                    <p class="ui-page-intro__lead">{{ $page->description }}</p>
                @endif
                @if ($page->type === 'legal' && $page->updatedAt !== null)
                    <p class="ui-note">{{ __('site.page.updated', ['date' => $page->updatedAt->locale($locale === 'ar' ? 'ar_JO' : 'en')->isoFormat('LL')]) }}</p>
                @endif
            </header>
            <div class="ui-prose">
                @foreach ($page->sections as $section)
                    @if ($section->type === 'faq')
                        {{-- #q-N = the section's position: search links straight to one answer (SearchIndexer). --}}
                        <x-ui.disclosure :summary="(string) $section->heading" class="ui-prose__faq" :id="'q-'.$loop->iteration" data-ui-reveal>
                            @foreach ($section->paragraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </x-ui.disclosure>
                    @elseif (in_array($section->type, ['list', 'steps'], true))
                        <section class="ui-prose__section" data-ui-reveal>
                            @if ($section->heading !== null)
                                <h2 class="ui-prose__heading">{{ $section->heading }}</h2>
                            @endif
                            @if ($section->type === 'steps')
                                <ol class="ui-steps" role="list">
                                    @foreach ($section->paragraphs as $item)
                                        <li class="ui-steps__item">{{ $item }}</li>
                                    @endforeach
                                </ol>
                            @else
                                <ul class="ui-prose__list">
                                    @foreach ($section->paragraphs as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @else
                        <section class="ui-prose__section" data-ui-reveal>
                            @if ($section->heading !== null)
                                <h2 class="ui-prose__heading">{{ $section->heading }}</h2>
                            @endif
                            @if ($section->image !== null)
                                <x-ui.picture :image="$section->image" ratio="landscape" sizes="(min-width: 1024px) 60vw, 100vw" />
                            @endif
                            @foreach ($section->paragraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </section>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endsection
