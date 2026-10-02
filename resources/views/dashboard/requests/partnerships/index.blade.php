@extends('layouts.dashboard')

@section('title', __('dashboard.requests.partnerships_title'))

@section('content')
    {{--
        Requests → Partnerships (docs/franchise/04 §1–§5): new and total, the count per stage (each opens its list),
        where the demand is (a plain table), then search, filters and the applications.
    --}}
    @php
        $link = fn (array $changes): string => route('dashboard.partnerships.index', array_filter(array_merge($filters, ['page' => null], $changes), fn ($v) => $v !== '' && $v !== null));
        $date = fn ($d) => $d->timezone('Asia/Amman')->format('Y-m-d');
    @endphp
    <x-ui.page-header :title="__('dashboard.requests.partnerships_title')" :description="__('dashboard.requests.partnerships_description')" />

    <div class="ui-tiles ui-inbox-cards">
        <x-ui.stat-tile :label="__('dashboard.requests.fr.cards.new')" :value="$counts['new']" :href="$link(['status' => 'new'])" />
        <x-ui.stat-tile :label="__('dashboard.requests.fr.cards.total')" :value="$counts['total']" :href="$link(['status' => ''])" />
    </div>

    <section class="ui-inbox-overview" aria-labelledby="stages-title">
        <h2 class="ui-record__title" id="stages-title">{{ __('dashboard.requests.fr.stages_title') }}</h2>
        <nav class="ui-filter-bar" aria-labelledby="stages-title">
            @foreach ($counts['stages'] as $stage => $n)
                <x-ui.chip :href="$link(['status' => $stage])" :current="$filters['status'] === $stage">{{ $labels[$stage] ?? $stage }} <bdi>({{ $n }})</bdi></x-ui.chip>
            @endforeach
        </nav>
    </section>

    <section class="ui-inbox-overview" aria-labelledby="demand-title">
        <h2 class="ui-record__title" id="demand-title">{{ __('dashboard.requests.fr.demand_title') }}</h2>
        <div class="ui-inbox-demand">
            @foreach (['country', 'city', 'market'] as $kind)
                <div>
                    <h3 class="ui-record__subtitle">{{ __('dashboard.requests.fr.demand.'.$kind) }}</h3>
                    @if ($demand[$kind] === [])
                        <p class="ui-note">{{ __('dashboard.requests.fr.demand_empty') }}</p>
                    @else
                        <dl class="ui-facts ui-inbox-demand__list">
                            @foreach ($demand[$kind] as $value => $n)
                                <div><dt>{{ $kind === 'country' ? ($countries[$value] ?? $value) : $value }}</dt><dd><bdi>{{ $n }}</bdi></dd></div>
                            @endforeach
                        </dl>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <form class="ui-inbox-filters" method="get" action="{{ route('dashboard.partnerships.index') }}" role="search">
        <div class="ui-inbox-filters__main">
            <x-ui.field :label="__('dashboard.requests.search')" for="q" :hint="__('dashboard.requests.fr.search_hint')">
                <x-ui.input type="search" id="q" name="q" :value="$filters['q']" maxlength="100" autocomplete="off" />
            </x-ui.field>
            <x-ui.field :label="__('dashboard.requests.fr.stage')" for="status">
                <x-ui.select id="status" name="status" :options="['' => __('dashboard.requests.fr.stage_all'), 'new' => __('dashboard.requests.new')] + $labels" :selected="$filters['status']" />
            </x-ui.field>
            <x-ui.field :label="__('dashboard.requests.sort')" for="sort">
                <x-ui.select id="sort" name="sort" :options="__('dashboard.requests.fr.sorts')" :selected="$filters['sort']" />
            </x-ui.field>
        </div>
        <x-ui.disclosure :summary="__('dashboard.requests.more_filters')" :open="$filters['country'] !== '' || $filters['from'] !== '' || $filters['to'] !== ''">
            <div class="ui-inbox-filters__more">
                <x-ui.field :label="__('dashboard.requests.fr.country')" for="country">
                    <x-ui.select id="country" name="country" :options="['' => __('dashboard.requests.filters.any')] + $countries" :selected="$filters['country']" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.filters.from')" for="from">
                    <x-ui.input type="date" id="from" name="from" :value="$filters['from']" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.filters.to')" for="to">
                    <x-ui.input type="date" id="to" name="to" :value="$filters['to']" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.per_page')" for="per">
                    <x-ui.select id="per" name="per" :options="array_combine(\App\Services\Requests\CareersQuery::PAGE_SIZES, \App\Services\Requests\CareersQuery::PAGE_SIZES)" :selected="(string) $filters['per']" />
                </x-ui.field>
            </div>
        </x-ui.disclosure>
        <div class="ui-inbox-filters__actions">
            <x-ui.button type="submit" icon="search">{{ __('dashboard.requests.apply') }}</x-ui.button>
            <x-ui.button variant="ghost" :href="route('dashboard.partnerships.index')">{{ __('dashboard.requests.reset') }}</x-ui.button>
        </div>
    </form>

    <p class="ui-inbox-count" role="status">{{ trans_choice('dashboard.requests.results', $total, ['count' => $total]) }}</p>

    @if ($items === [])
        <x-ui.empty-state :title="__('dashboard.requests.empty')" icon="handshake" />
    @else
        <x-ui.table :caption="__('dashboard.requests.partnerships_title')" stack="wide" class="ui-inbox-table">
            <thead role="rowgroup">
                <tr role="row">
                    @foreach (['applicant', 'country', 'city', 'market', 'date', 'stage', 'updated'] as $column)
                        <th scope="col" role="columnheader">{{ __('dashboard.requests.fr.columns.'.$column) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody role="rowgroup">
                @foreach ($items as $application)
                    @php $p = $application->partnership; @endphp
                    <tr role="row" @class(['ui-inbox-row--new' => $application->isNew()])>
                        <th scope="row" role="rowheader" data-label="{{ __('dashboard.requests.fr.columns.applicant') }}">
                            <span class="ui-inbox-name">
                                <a href="{{ route('dashboard.partnerships.show', $application) }}">{{ $p?->full_name }}</a>
                                @if ($application->isNew())
                                    <x-ui.badge variant="info">{{ __('dashboard.requests.new') }}</x-ui.badge>
                                @endif
                            </span>
                        </th>
                        <td role="cell" data-label="{{ __('dashboard.requests.fr.columns.country') }}">{{ $p ? ($countries[$p->country_code] ?? $p->country_code) : '' }}</td>
                        <td role="cell" data-label="{{ __('dashboard.requests.fr.columns.city') }}">{{ $p?->city_text }}</td>
                        <td role="cell" data-label="{{ __('dashboard.requests.fr.columns.market') }}">{{ $p?->market_interest }}</td>
                        <td role="cell" data-label="{{ __('dashboard.requests.fr.columns.date') }}"><bdi>{{ $date($application->submitted_at) }}</bdi></td>
                        <td role="cell" data-label="{{ __('dashboard.requests.fr.columns.stage') }}"><x-ui.badge>{{ $labels[$application->status] ?? $application->status }}</x-ui.badge></td>
                        <td role="cell" data-label="{{ __('dashboard.requests.fr.columns.updated') }}"><bdi>{{ $date($application->updated_at) }}</bdi></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :current="$current" :total="$last" :url="fn (int $page) => $link(['page' => $page])" />
    @endif
@endsection
