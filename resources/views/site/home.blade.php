@extends('layouts.site')
@php($description = filled($description ?? null) ? $description : __('site.meta.home'))

@section('title', __('site.home.title'))

@section('content')
    @php($locale = app()->getLocale())
    {{-- SI-B02. Copy is functional DRAFT wording (D-068); no photography until media is approved. --}}
    <x-ui.hero
        :lines="explode(' ', $locale === 'ar' ? __('site.brand_ar') : __('site.brand'))"
        :eyebrow="$locale === 'ar' ? __('site.brand') : __('site.brand_ar')"
        :eyebrow-lang="$locale === 'ar' ? 'en' : 'ar'"
        :lead="__('site.home.lead')">
        @if ($menuUrl)
            <x-ui.button size="lg" :href="$menuUrl" icon-end="arrow-right">{{ __('site.home.cta_menu') }}</x-ui.button>
        @endif
        @if ($locationsUrl)
            <x-ui.button size="lg" :variant="$menuUrl ? 'outline' : 'primary'" :href="$locationsUrl" :icon-end="$menuUrl ? null : 'arrow-right'">{{ __('site.home.cta_locations') }}</x-ui.button>
        @endif
        @if (count($branches) > 0)
            <x-slot:aside>
                <section class="ui-home-branches" aria-labelledby="home-branches">
                    <x-ui.section-heading id="home-branches" :title="__('site.home.branches_title')" :lead="__('site.home.branches_lead')" />
                    <ul class="ui-branch-list" role="list">
                        @foreach ($branches as $branch)
                            <li><x-ui.branch-card :branch="$branch" :details-label="__('site.locations.details')" /></li>
                        @endforeach
                    </ul>
                </section>
            </x-slot:aside>
        @endif
    </x-ui.hero>

    {{-- The home feature placement (DX-010): the campaign or announcement the engine picks now — or nothing (DX-012) —
         with its image only when the Owner chose an approved one (CAMP-004, MEDIA-RIGHTS). --}}
    @if ($feature !== null)
        @php($featureImage = $feature->image)
        <section class="ui-band" aria-labelledby="home-feature" data-experience="{{ $feature->id }}">
            <div @class(['ui-container', 'ui-feature' => $featureImage !== null])>
                @if ($featureImage !== null)
                    <x-ui.picture :image="$featureImage" ratio="landscape" sizes="(min-width: 768px) 50vw, 100vw" class="ui-feature__media" />
                @endif
                <x-ui.section-heading id="home-feature" :eyebrow="__('site.home.feature.'.$feature->type)" :title="$feature->title" :lead="$feature->text"
                    :href="$feature->ctaUrl" :link-label="$feature->ctaLabel" />
            </div>
        </section>
    @endif

    {{-- Employee of the Month (DX-009), when the Owner placed it on the home page — or nothing (DX-012). --}}
    @if ($recognition !== null)
        <section class="ui-band" aria-labelledby="recognition-{{ $recognition->id }}">
            <div class="ui-container">
                @include('site.partials.recognition', ['recognition' => $recognition])
            </div>
        </section>
    @endif


@endsection
