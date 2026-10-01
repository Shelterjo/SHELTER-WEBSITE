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
                        <x-ui.disclosure :summary="(string) $section->heading" class="ui-prose__faq" data-ui-reveal>
                            @foreach ($section->paragraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </x-ui.disclosure>
                    @else
                        <section class="ui-prose__section" data-ui-reveal>
                            @if ($section->heading !== null)
                                <h2 class="ui-prose__heading">{{ $section->heading }}</h2>
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
