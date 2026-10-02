@extends('layouts.dashboard')

@section('title', __('dashboard.requests.careers_title'))

@section('content')
    {{--
        Requests → Job applications (CAREERS-052…075): the period cards (each opens its list), live search, the status
        filter and sort, more filters on demand, saved searches, the Owner's own columns / density / rows per page, then
        the applications (a table from 1200px, cards below) with bulk actions, quick view and a nudge on stale ones.
    --}}
    @php
        $badge = ['received' => 'info', 'under_review' => 'neutral', 'interview_shortlisted' => 'warning', 'interviewed' => 'neutral', 'accepted' => 'success', 'rejected' => 'danger', 'archived' => 'neutral'];
        $link = fn (array $changes): string => route('dashboard.careers.index', array_filter(array_merge($filters, ['page' => null], $changes), fn ($v) => $v !== '' && $v !== null));
        $options = fn (string $group): array => ['' => __('dashboard.requests.filters.any')] + __('dashboard.requests.options.'.$group);
        $date = fn ($d) => $d->timezone('Asia/Amman')->format('Y-m-d');
    @endphp
    <x-ui.page-header :title="__('dashboard.requests.careers_title')" :description="__('dashboard.requests.careers_description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" size="sm" :href="route('dashboard.careers.settings')">{{ __('dashboard.requests.settings.title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <nav class="ui-filter-bar" aria-label="{{ __('dashboard.requests.period_label') }}">
        @foreach (\App\Services\Requests\CareersQuery::PERIODS as $period)
            <x-ui.chip :href="$link(['period' => $period])" :current="$filters['period'] === $period">{{ __('dashboard.requests.periods.'.$period) }}</x-ui.chip>
        @endforeach
    </nav>
    <div class="ui-tiles ui-inbox-cards">
        @foreach ($counts as $card => $value)
            <x-ui.stat-tile :label="__('dashboard.requests.cards.'.$card)" :value="$value" :href="$link(['status' => $card === 'total' ? '' : $card])" />
        @endforeach
    </div>

    <form class="ui-inbox-filters" method="get" action="{{ route('dashboard.careers.index') }}" role="search">
        <input type="hidden" name="period" value="{{ $filters['period'] }}">
        <div class="ui-inbox-filters__main">
            <x-ui.field :label="__('dashboard.requests.search')" for="q" :hint="__('dashboard.requests.search_hint')">
                <x-ui.input type="search" id="q" name="q" :value="$filters['q']" maxlength="100" autocomplete="off" />
            </x-ui.field>
            <x-ui.field :label="__('dashboard.requests.status_filter')" for="status">
                <x-ui.select id="status" name="status" :options="['' => __('dashboard.requests.status_all'), 'new' => __('dashboard.requests.new')] + __('dashboard.requests.statuses')" :selected="$filters['status']" />
            </x-ui.field>
            <x-ui.field :label="__('dashboard.requests.sort')" for="sort">
                <x-ui.select id="sort" name="sort" :options="__('dashboard.requests.sorts')" :selected="$filters['sort']" />
            </x-ui.field>
        </div>
        <x-ui.disclosure :summary="__('dashboard.requests.more_filters')" :open="$advanced">
            <div class="ui-inbox-filters__more">
                <x-ui.field :label="__('dashboard.requests.filters.city')" for="city">
                    <x-ui.select id="city" name="city" :options="['' => __('dashboard.requests.filters.any')] + $cities->mapWithKeys(fn ($c) => [$c->id => app()->getLocale() === 'ar' ? $c->name_ar : ($c->name_en ?? $c->name_ar)])->all()" :selected="(string) $filters['city']" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.filters.gender')" for="gender">
                    <x-ui.select id="gender" name="gender" :options="$options('gender')" :selected="$filters['gender']" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.filters.nationality')" for="nationality">
                    <x-ui.select id="nationality" name="nationality" :options="$options('nationality_type')" :selected="$filters['nationality']" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.filters.education')" for="education">
                    <x-ui.select id="education" name="education" :options="$options('education_level')" :selected="$filters['education']" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.filters.experience')" for="experience">
                    <x-ui.select id="experience" name="experience" :options="$options('experience_band')" :selected="$filters['experience']" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.requests.filters.job')" for="job">
                    <x-ui.input id="job" name="job" :value="$filters['job']" maxlength="100" />
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
            <x-ui.button variant="ghost" :href="route('dashboard.careers.index', ['period' => $filters['period']])">{{ __('dashboard.requests.reset') }}</x-ui.button>
        </div>
    </form>

    {{-- Saved searches (CAREERS-060): one press opens the same filters again. --}}
    @if ($saved !== [])
        <nav class="ui-filter-bar" aria-label="{{ __('dashboard.requests.saved.title') }}">
            @foreach ($saved as $item)
                <span class="ui-saved-filter">
                    <x-ui.chip :href="route('dashboard.careers.index', $item['query'])">{{ $item['name'] }}</x-ui.chip>
                    <form method="post" action="{{ route('dashboard.careers.filters.destroy', $item['id']) }}">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" size="sm" variant="ghost" icon="x" :aria-label="__('dashboard.requests.saved.remove', ['name' => $item['name']])"><span class="ui-visually-hidden">{{ __('dashboard.requests.saved.remove', ['name' => $item['name']]) }}</span></x-ui.button>
                    </form>
                </span>
            @endforeach
        </nav>
    @endif

    <div class="ui-inbox-tools">
        <x-ui.disclosure :summary="__('dashboard.requests.saved.save')">
            <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.filters.store') }}">
                @csrf
                @foreach (array_filter($filters, fn ($v, $k) => $v !== '' && $v !== null && $k !== 'per', ARRAY_FILTER_USE_BOTH) as $key => $value)
                    <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
                @endforeach
                <x-ui.field :label="__('dashboard.requests.saved.name')" for="saved-name" :hint="__('dashboard.requests.saved.hint')" :error="$errors->first('saved_name')">
                    <x-ui.input id="saved-name" name="name" maxlength="120" />
                </x-ui.field>
                <x-ui.button type="submit" variant="secondary">{{ __('dashboard.requests.saved.save_button') }}</x-ui.button>
            </form>
        </x-ui.disclosure>
        <x-ui.disclosure :summary="__('dashboard.requests.view.title')" :open="session()->has('view_open')">
            <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.view') }}">
                @csrf
                <input type="hidden" name="back" value="{{ request()->fullUrl() }}">
                <x-ui.fieldset :legend="__('dashboard.requests.view.density')" id="density">
                    <div class="ui-editor__options">
                        @foreach (\App\Services\Requests\CareersView::DENSITIES as $option)
                            <x-ui.radio :label="__('dashboard.requests.view.densities.'.$option)" name="density" :value="$option" :id="'density-'.$option" :checked="$density === $option" />
                        @endforeach
                    </div>
                </x-ui.fieldset>
                <x-ui.fieldset :legend="__('dashboard.requests.view.columns')" id="columns">
                    <p class="ui-note">{{ __('dashboard.requests.view.drag_hint') }}</p>
                    <ul class="ui-column-list" role="list" data-column-list>
                        <li class="ui-column-list__item"><x-ui.checkbox :label="__('dashboard.requests.columns.name')" id="col-name" checked disabled /></li>
                        @foreach ($ordered as $column)
                            @php $isShown = in_array($column, $columns, true); @endphp
                            <li class="ui-column-list__item" data-column="{{ $column }}">
                                <x-ui.checkbox :label="__('dashboard.requests.columns.'.$column)" name="shown[]" :value="$column" :id="'col-'.$column" :checked="$isShown" />
                                @if ($isShown)
                                    <span class="ui-column-list__moves">
                                        <x-ui.button type="submit" size="sm" variant="ghost" name="move" :value="$column.':up'" :disabled="$loop->first">{{ __('dashboard.requests.view.up') }}<span class="ui-visually-hidden"> — {{ __('dashboard.requests.columns.'.$column) }}</span></x-ui.button>
                                        <x-ui.button type="submit" size="sm" variant="ghost" name="move" :value="$column.':down'" :disabled="$loop->index === count($columns) - 1">{{ __('dashboard.requests.view.down') }}<span class="ui-visually-hidden"> — {{ __('dashboard.requests.columns.'.$column) }}</span></x-ui.button>
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </x-ui.fieldset>
                <div class="ui-record__archive">
                    <x-ui.button type="submit" variant="secondary">{{ __('dashboard.requests.view.apply') }}</x-ui.button>
                    <x-ui.button type="submit" variant="ghost" name="reset" value="1">{{ __('dashboard.requests.view.reset') }}</x-ui.button>
                </div>
            </form>
        </x-ui.disclosure>
    </div>

    {{-- The results: replaced in place by the live search (CAREERS-058) without moving the focus out of the search box. --}}
    <div class="ui-inbox-results" data-careers-results>
        <div class="ui-inbox-bar">
            <p class="ui-inbox-count" role="status">{{ trans_choice('dashboard.requests.results', $total, ['count' => $total]) }}</p>
            @if ($total > 0)
                <x-ui.button size="sm" variant="outline" icon="upload" :href="route('dashboard.careers.export', array_filter(['scope' => 'filtered'] + $filters, fn ($v) => $v !== '' && $v !== null))">{{ __('dashboard.requests.export.open') }}</x-ui.button>
            @endif
        </div>

        @if ($items === [])
            <x-ui.empty-state :title="__('dashboard.requests.empty')" icon="briefcase-business" />
        @else
            {{-- Several at once (CAREERS-068): tick rows, choose what to do; a status change asks once, clearly. --}}
            <form class="ui-bulk-bar" id="bulk" method="post" action="{{ route('dashboard.careers.bulk') }}" data-bulk data-bulk-forms="{{ json_encode(__('dashboard.requests.bulk.count_forms'), JSON_UNESCAPED_UNICODE) }}">
                @csrf
                <x-ui.field :label="__('dashboard.requests.bulk.label')" for="bulk-action">
                    <x-ui.select id="bulk-action" name="action" :options="['' => __('dashboard.requests.bulk.choose_short')]
                        + collect(\App\Services\Requests\ApplicationInbox::statuses('JOB'))->mapWithKeys(fn ($s) => ['status:'.$s => __('dashboard.requests.bulk.to', ['status' => __('dashboard.requests.statuses.'.$s)])])->all()
                        + ['export' => __('dashboard.requests.bulk.export'), 'zip' => __('dashboard.requests.bulk.zip')]" />
                </x-ui.field>
                <x-ui.button type="submit" variant="secondary">{{ __('dashboard.requests.bulk.go') }}</x-ui.button>
                <x-ui.checkbox :label="__('dashboard.requests.bulk.all')" id="select-all" data-select-all />
                <p class="ui-note" data-bulk-count aria-live="polite"></p>
            </form>
            <x-ui.table :caption="__('dashboard.requests.careers_title')" stack="wide" :class="$density === 'compact' ? 'ui-inbox-table ui-inbox-table--compact' : 'ui-inbox-table'">
                <thead role="rowgroup">
                    <tr role="row">
                        <th scope="col" role="columnheader" class="ui-select-col"><span class="ui-visually-hidden">{{ __('dashboard.requests.bulk.select') }}</span></th>
                        <th scope="col" role="columnheader">{{ __('dashboard.requests.columns.name') }}</th>
                        @foreach ($columns as $column)
                            <th scope="col" role="columnheader" @class(['ui-table__numeric' => $column === 'salary'])>{{ __('dashboard.requests.columns.'.$column) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody role="rowgroup">
                    @foreach ($items as $application)
                        @php $job = $application->job; @endphp
                        <tr role="row" @class(['ui-inbox-row--new' => $application->isNew()])>
                            <td role="cell" class="ui-select-col" data-label="{{ __('dashboard.requests.bulk.select') }}">
                                <label class="ui-select-cell"><input class="ui-check__input" type="checkbox" form="bulk" name="ids[]" value="{{ $application->id }}" data-select-item
                                    aria-label="{{ __('dashboard.requests.bulk.select_one', ['name' => $job?->full_name ?? $application->reference_number]) }}"></label>
                            </td>
                            <th scope="row" role="rowheader" data-label="{{ __('dashboard.requests.columns.name') }}">
                                <span class="ui-inbox-name">
                                    <a href="{{ route('dashboard.careers.show', $application) }}" data-quick-view="{{ route('dashboard.careers.quick', $application) }}">{{ $job?->full_name }}</a>
                                    @if ($application->isNew())
                                        <x-ui.badge variant="info">{{ __('dashboard.requests.new') }}</x-ui.badge>
                                    @endif
                                    @if ($application->updated_at !== null && $application->updated_at->lessThan($staleBefore) && ! in_array($application->status, ['accepted', 'rejected', 'archived'], true))
                                        <x-ui.badge variant="warning" icon="clock">{{ __('dashboard.requests.stale') }}</x-ui.badge>
                                    @endif
                                </span>
                            </th>
                            @foreach ($columns as $column)
                                <td role="cell" data-label="{{ __('dashboard.requests.columns.'.$column) }}" @class(['ui-table__numeric' => $column === 'salary'])>
                                    @switch($column)
                                        @case('job') {{ $job?->job_title_text }} @break
                                        @case('city') {{ $job?->city ? (app()->getLocale() === 'ar' ? $job->city->name_ar : ($job->city->name_en ?? $job->city->name_ar)) : '' }} @break
                                        @case('experience') {{ $job ? __('dashboard.requests.options.experience_band.'.$job->experience_band) : '' }} @break
                                        @case('salary') <bdi>{{ $job ? __('dashboard.requests.salary', ['amount' => rtrim(rtrim((string) $job->expected_salary_jod, '0'), '.')]) : '' }}</bdi> @break
                                        @case('date') <bdi>{{ $date($application->submitted_at) }}</bdi> @break
                                        @case('status') <x-ui.badge :variant="$badge[$application->status] ?? 'neutral'">{{ __('dashboard.requests.statuses.'.$application->status) }}</x-ui.badge> @break
                                        @case('reference') <bdi>{{ $application->reference_number }}</bdi> @break
                                        @case('phone') <bdi dir="ltr">{{ $job?->phone_normalized }}</bdi> @break
                                        @case('email') <bdi dir="ltr">{{ $job?->email }}</bdi> @break
                                        @case('education') {{ $job ? __('dashboard.requests.options.education_level.'.$job->education_level) : '' }} @break
                                        @case('gender') {{ $job ? __('dashboard.requests.options.gender.'.$job->gender) : '' }} @break
                                        @case('nationality') {{ $job ? __('dashboard.requests.options.nationality_type.'.$job->nationality_type) : '' }} @break
                                        @case('updated') <bdi>{{ $application->updated_at !== null ? $date($application->updated_at) : '' }}</bdi> @break
                                    @endswitch
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
            <x-ui.pagination :current="$current" :total="$last" :url="fn (int $page) => $link(['page' => $page])" />
        @endif
    </div>

    {{-- Quick view (CAREERS-065): filled by the list's script; without it the name opens the full page. --}}
    <x-ui.drawer id="quick-view" :title="__('dashboard.requests.quick.title')" data-quick-view-panel>
        <div data-quick-target></div>
    </x-ui.drawer>
@endsection
