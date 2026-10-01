@extends('layouts.site')

@section('title', __('menu.page_title', ['brand' => __('site.brand')]))

@section('content')
    @php
        $branchOptions = ['all' => __('menu.all_branches')];
        foreach ($menu->branches as $option) {
            $branchOptions[$option->slug] = ['label' => $option->label, 'lang' => 'en'];
        }
        $navItems = array_map(fn ($section): array => [
            'href' => '#'.$section->id,
            'label' => $section->name,
            'lang' => $section->nameLang,
            'attributes' => ['data-ui-menu-nav' => $section->id],
        ], $menu->allSections());
    @endphp
    {{-- SI-M02 · Menu IA spec: one page, approved names and VAT-inclusive prices only; nothing about availability while
         every branch is UNKNOWN (CF-02). Without JavaScript the full menu is readable and every link works. --}}
    <div class="ui-page ui-menu" data-ui-menu data-branch="{{ $menu->branch }}">
        <div class="ui-container">
            <x-ui.breadcrumb :items="$crumbs" />
            <header class="ui-page-intro">
                <h1 class="ui-page-intro__title">{{ __('menu.title') }}</h1>
                <p class="ui-page-intro__lead">{{ __('menu.prices_note') }}</p>
            </header>

            <div class="ui-menu__tools">
                <x-ui.search-field id="menu-search" :label="__('menu.search_label')" :placeholder="__('menu.search_placeholder')" data-ui-menu-search />
                @if (count($menu->branches) > 1)
                    <div class="ui-menu__branch">
                        <x-ui.segmented :label="__('menu.branch_label')" :options="$branchOptions" :selected="$menu->branch" data-ui-menu-branch />
                        <ul class="ui-menu__statuses" role="list">
                            @foreach ($menu->branches as $option)
                                @if ($option->timeline !== null)
                                    <li class="ui-menu__status" data-ui-menu-status="{{ $option->slug }}" @if (! in_array($menu->branch, ['all', $option->slug], true)) hidden @endif>
                                        <span class="ui-menu__status-label" lang="en" dir="ltr">{{ $option->label }}</span>
                                        <x-ui.open-status :timeline="$option->timeline" />
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="ui-menu__layout">
                <aside class="ui-menu__nav">
                    <x-ui.category-nav id="menu-categories" :label="__('menu.categories_label')" :items="$navItems" sidebar />
                </aside>

                <div class="ui-menu__content">
                    <p class="ui-menu__count" data-ui-menu-count aria-live="polite" hidden></p>

                    <div class="ui-menu__empty" data-ui-menu-empty hidden>
                        <x-ui.empty-state :title="__('menu.no_results_title', ['query' => ''])">
                            {{ __('menu.no_results_text') }}
                            <x-slot:actions>
                                <x-ui.button variant="secondary" data-ui-menu-clear>{{ __('menu.clear_search') }}</x-ui.button>
                                <x-ui.button variant="ghost" href="#menu-categories">{{ __('menu.browse_categories') }}</x-ui.button>
                            </x-slot:actions>
                        </x-ui.empty-state>
                    </div>

                    @foreach ($menu->allSections() as $section)
                        @php($isSeason = $menu->season !== null && $section === $menu->season)
                        <section @class(['ui-menu__section', 'ui-menu__section--season' => $isSeason]) id="{{ $section->id }}" aria-labelledby="{{ $section->id }}-title" data-ui-menu-section>
                            <div class="ui-menu__section-head">
                                <h2 class="ui-menu__section-title" id="{{ $section->id }}-title">
                                    <span @if ($section->nameLang) lang="{{ $section->nameLang }}" dir="{{ $section->nameLang === 'ar' ? 'rtl' : 'ltr' }}" @endif>{{ $section->name }}</span>
                                </h2>
                                <p class="ui-menu__section-count">{{ trans_choice('menu.items_count', $section->count(), ['count' => $section->count()]) }}</p>
                            </div>
                            @foreach ($section->groups as $group)
                                @if ($group->name !== null)
                                    <h3 class="ui-menu__group-title" id="{{ $group->id }}">
                                        <span @if ($group->nameLang) lang="{{ $group->nameLang }}" dir="{{ $group->nameLang === 'ar' ? 'rtl' : 'ltr' }}" @endif>{{ $group->name }}</span>
                                    </h3>
                                @endif
                                <ul @class(['ui-grid', 'ui-menu__grid', 'ui-menu__grid--list' => $isSeason]) role="list">
                                    @foreach ($group->items as $item)
                                        <li data-ui-menu-item="{{ $item->anchor() }}">
                                            <x-ui.product-card
                                                :id="$item->anchor()"
                                                :name="$item->name"
                                                :name-lang="$item->nameLang"
                                                :secondary="$item->secondary"
                                                :secondary-lang="$item->secondaryLang"
                                                :price="$item->priceFils"
                                                :currency="$item->currency"
                                                :badge="$isSeason ? __('menu.seasonal') : null"
                                                :level="0"
                                                :compact="$isSeason"
                                                opens="menu-detail"
                                                data-location="{{ $item->location }}" />
                                        </li>
                                    @endforeach
                                </ul>
                            @endforeach
                        </section>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- One detail dialog for every product: a bottom sheet on phones, a centred modal from 1024px (spec §7). The
             script fills it from the clicked card; without JavaScript the card already shows everything we have. --}}
        <x-ui.bottom-sheet id="menu-detail" :title="__('menu.title')" adaptive data-ui-menu-detail>
            <div class="ui-menu-detail">
                <p class="ui-menu-detail__location" data-detail-location></p>
                <p class="ui-menu-detail__secondary" data-detail-secondary></p>
                <p class="ui-menu-detail__price" data-detail-price></p>
            </div>
        </x-ui.bottom-sheet>

        <script type="application/json" id="menu-index">@json($searchIndex)</script>
        <script type="application/json" id="menu-messages">@json(['results' => __('menu.results_forms'), 'noResults' => __('menu.no_results_title'), 'seasonal' => __('menu.seasonal')])</script>
    </div>
@endsection
