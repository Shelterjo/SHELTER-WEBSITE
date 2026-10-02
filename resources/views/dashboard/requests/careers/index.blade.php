@extends('layouts.dashboard')

@section('title', __('dashboard.requests.careers_title'))

@section('content')
    {{--
        Requests → Job applications (CAREERS-052…064): the period cards (each opens its list), quick search, the status
        filter and sort, more filters on demand, then the applications — a table from 600px, cards below.
    --}}
    @php
        $badge = ['received' => 'info', 'under_review' => 'neutral', 'interview_shortlisted' => 'warning', 'interviewed' => 'neutral', 'accepted' => 'success', 'rejected' => 'danger', 'archived' => 'neutral'];
        $link = fn (array $changes): string => route('dashboard.careers.index', array_filter(array_merge($filters, ['page' => null], $changes), fn ($v) => $v !== '' && $v !== null));
        $options = fn (string $group): array => ['' => __('dashboard.requests.filters.any')] + __('dashboard.requests.options.'.$group);
        $date = fn ($d) => $d->timezone('Asia/Amman')->format('Y-m-d');
    @endphp
    <x-ui.page-header :title="__('dashboard.requests.careers_title')" :description="__('dashboard.requests.careers_description')" />

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
        <x-ui.table :caption="__('dashboard.requests.careers_title')" stack="wide" class="ui-inbox-table">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col" role="columnheader" class="ui-select-col"><span class="ui-visually-hidden">{{ __('dashboard.requests.bulk.select') }}</span></th>
                    @foreach (['name', 'job', 'city', 'experience', 'salary', 'date', 'status'] as $column)
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
                                <a href="{{ route('dashboard.careers.show', $application) }}">{{ $job?->full_name }}</a>
                                @if ($application->isNew())
                                    <x-ui.badge variant="info">{{ __('dashboard.requests.new') }}</x-ui.badge>
                                @endif
                            </span>
                        </th>
                        <td role="cell" data-label="{{ __('dashboard.requests.columns.job') }}">{{ $job?->job_title_text }}</td>
                        <td role="cell" data-label="{{ __('dashboard.requests.columns.city') }}">{{ $job?->city ? (app()->getLocale() === 'ar' ? $job->city->name_ar : ($job->city->name_en ?? $job->city->name_ar)) : '' }}</td>
                        <td role="cell" data-label="{{ __('dashboard.requests.columns.experience') }}">{{ $job ? __('dashboard.requests.options.experience_band.'.$job->experience_band) : '' }}</td>
                        <td role="cell" data-label="{{ __('dashboard.requests.columns.salary') }}" class="ui-table__numeric"><bdi>{{ $job ? __('dashboard.requests.salary', ['amount' => rtrim(rtrim((string) $job->expected_salary_jod, '0'), '.')]) : '' }}</bdi></td>
                        <td role="cell" data-label="{{ __('dashboard.requests.columns.date') }}"><bdi>{{ $date($application->submitted_at) }}</bdi></td>
                        <td role="cell" data-label="{{ __('dashboard.requests.columns.status') }}"><x-ui.badge :variant="$badge[$application->status] ?? 'neutral'">{{ __('dashboard.requests.statuses.'.$application->status) }}</x-ui.badge></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :current="$current" :total="$last" :url="fn (int $page) => $link(['page' => $page])" />
    @endif
@endsection
