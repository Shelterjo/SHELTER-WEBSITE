@extends('layouts.site')

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
    </x-ui.hero>

    {{-- The home feature placement (DX-010): the campaign or announcement the engine picks now — or nothing (DX-012). --}}
    @if ($feature !== null)
        <section class="ui-band" aria-labelledby="home-feature" data-experience="{{ $feature->id }}">
            <div class="ui-container">
                <x-ui.section-heading id="home-feature" :eyebrow="__('site.home.feature.'.$feature->type)" :title="$feature->title" :lead="$feature->text"
                    :href="$feature->ctaUrl" :link-label="$feature->ctaLabel" />
            </div>
        </section>
    @endif

    @if (count($branches) > 0)
        <section class="ui-band" aria-labelledby="home-branches" data-ui-reveal>
            <div class="ui-container">
                <x-ui.section-heading id="home-branches" :title="__('site.home.branches_title')" :lead="__('site.home.branches_lead')"
                    :href="$locationsUrl" :link-label="__('site.home.branches_link')" />
                <ul class="ui-branch-list" role="list">
                    @foreach ($branches as $branch)
                        <li><x-ui.branch-card :branch="$branch" /></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
