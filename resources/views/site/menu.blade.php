@extends('layouts.site')
@php
    $description = filled($description ?? null) ? $description : __('site.meta.menu');
@endphp

@section('title', __('menu.page_title', ['brand' => __('site.brand')]))

@section('content')
    @php
        $branchOptions = ['all' => __('menu.all_branches')];
        foreach ($menu->branches as $option) {
            $branchOptions[$option->slug] = ['label' => $option->label, 'lang' => 'en'];
        }
        // Per-branch display (§9.6): what each card shows for the chosen branch; a section whose items are all hidden
        // there disappears with its chip. The script switches branches with the same data (data-ui-menu-branches).
        $labels = [];
        foreach ($menu->branches as $option) {
            $labels[$option->slug] = $option->label;
        }
        $display = [];
        $emptySections = [];
        foreach ($menu->allSections() as $section) {
            $shown = 0;
            foreach ($section->groups as $group) {
                foreach ($group->items as $item) {
                    $display[$item->code] = $item->display($menu->branch, $labels);
                    $shown += $display[$item->code]['hidden'] ? 0 : 1;
                }
            }
            if ($shown === 0) {
                $emptySections[$section->id] = true;
            }
        }
        $navItems = array_map(fn ($section): array => [
            'href' => '#'.$section->id,
            'label' => $section->name,
            'lang' => $section->nameLang,
            'attributes' => ['data-ui-menu-nav' => $section->id],
            'hidden' => isset($emptySections[$section->id]),
        ], $menu->allSections());
    @endphp
    {{-- SI-M02 · Menu IA spec: one page, approved names and VAT-inclusive prices only; availability only where the Owner
         set it (UNKNOWN says nothing — CF-02, §9.6). Without JavaScript the full menu is readable and every link works. --}}
    <div class="ui-page ui-menu" data-ui-menu data-branch="{{ $menu->branch }}" data-track-view="menu_view">
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
                        @php $isSeason = $menu->season !== null && $section === $menu->season; @endphp
                        <section @class(['ui-menu__section', 'ui-menu__section--season' => $isSeason]) id="{{ $section->id }}" aria-labelledby="{{ $section->id }}-title" data-ui-menu-section @if (isset($emptySections[$section->id])) hidden @endif>
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
                                @php
                                    // A section where some items have an approved image keeps the image space on every card (§6 (8)).
                                    $mixed = collect($group->items)->contains(fn ($i) => $i->image !== null);
                                @endphp
                                <ul @class(['ui-grid', 'ui-menu__grid', 'ui-menu__grid--list' => $isSeason]) role="list">
                                    @foreach ($group->items as $item)
                                        @php
                                            $d = $display[$item->code];
                                            $varies = $item->variesByBranch();
                                            $perBranch = [];
                                            if ($varies) {
                                                foreach (array_merge(['all'], array_keys($labels)) as $choice) {
                                                    $v = $item->display($choice, $labels);
                                                    $perBranch[$choice] = ['h' => $v['hidden'], 's' => $v['status'], 'u' => $v['unavailable'], 'p' => $v['price']];
                                                }
                                            }
                                        @endphp
                                        <li data-ui-menu-item="{{ $item->anchor() }}" @if ($varies) data-ui-menu-branches="{{ json_encode($perBranch, JSON_UNESCAPED_UNICODE) }}" @endif @if ($d['hidden']) hidden data-branch-hidden="true" @endif>
                                            <x-ui.product-card
                                                :id="$item->anchor()"
                                                :name="$item->name"
                                                :name-lang="$item->nameLang"
                                                :secondary="$item->secondary"
                                                :secondary-lang="$item->secondaryLang"
                                                :price="$d['price']"
                                                :currency="$item->currency"
                                                :badge="$isSeason ? __('menu.seasonal') : ($item->isNew ? __('menu.new') : null)"
                                                :status="$varies ? ($d['status'] ?? '') : null"
                                                :unavailable="$d['unavailable']"
                                                :label="$item->ariaLabel"
                                                :level="0"
                                                :compact="$isSeason"
                                                :reserve-media="$mixed && $item->image === null && ! $isSeason"
                                                opens="menu-detail"
                                                data-location="{{ $item->location }}"
                                                :data-description="$item->description">
                                                @if ($item->image !== null && ! $isSeason)
                                                    <x-slot:media>
                                                        <x-ui.picture :image="$item->image" ratio="square" sizes="(min-width: 1200px) 18vw, (min-width: 600px) 30vw, 45vw" />
                                                    </x-slot:media>
                                                @endif
                                            </x-ui.product-card>
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
                <div class="ui-menu-detail__media" data-detail-media hidden></div>
                <p class="ui-menu-detail__location" data-detail-location></p>
                <p class="ui-menu-detail__secondary" data-detail-secondary></p>
                <p class="ui-menu-detail__price" data-detail-price></p>
                <p class="ui-menu-detail__description" data-detail-description hidden></p>
            </div>
        </x-ui.bottom-sheet>

        <script type="application/json" id="menu-index">@json($searchIndex)</script>
        <script type="application/json" id="menu-messages">@json(['results' => __('menu.results_forms'), 'noResults' => __('menu.no_results_title'), 'seasonal' => __('menu.seasonal')])</script>
    </div>
@endsection
