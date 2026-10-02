@extends('layouts.dashboard')

@section('title', __('dashboard.requests.feedback.title'))

@section('content')
    {{--
        Requests → Customer feedback (VOICE-OF-CUSTOMER §5): period and branch, the average of each question with its
        count, branches compared, the last weeks, recurring topics, then the comments — archive, or remove personal data.
    --}}
    @php
        $F = 'dashboard.requests.feedback.';
        $link = fn (array $changes): string => route('dashboard.feedback.index', array_filter(array_merge([
            'period' => $filters['period'], 'branch' => $filters['branch'], 'low' => $filters['low'] ? '1' : null, 'show' => $filters['archived'] ? 'archived' : null,
        ], $changes), fn ($v) => $v !== null && $v !== ''));
        // In the tables an empty cell is a dash; a number shows its count (n) beside it.
        $cell = fn (array $s): string => $s['avg'] === null ? '—' : "\u{2066}".number_format($s['avg'], 1).' ('.$s['n'].")\u{2069}";
        // Left-to-right isolation keeps "from – to" in reading order inside Arabic text.
        $range = fn ($w): string => "\u{2066}".$w['from']->format('m-d').' – '.$w['to']->format('m-d')."\u{2069}";
        $dimensions = \App\Models\Feedback::DIMENSIONS;
    @endphp
    <x-ui.page-header :title="__($F.'title')" :description="__($F.'description')" />

    <nav class="ui-filter-bar" aria-label="{{ __('dashboard.requests.period_label') }}">
        @foreach (\App\Services\Requests\FeedbackBoard::PERIODS as $period)
            <x-ui.chip :href="$link(['period' => $period, 'page' => null])" :current="$filters['period'] === $period">{{ __($F.'periods.'.$period) }}</x-ui.chip>
        @endforeach
    </nav>
    <nav class="ui-filter-bar" aria-label="{{ __($F.'branch') }}">
        <x-ui.chip :href="$link(['branch' => null, 'page' => null])" :current="$filters['branch'] === null">{{ __($F.'all_branches') }}</x-ui.chip>
        @foreach ($branches as $id => $name)
            <x-ui.chip :href="$link(['branch' => $id, 'page' => null])" :current="$filters['branch'] === $id">{{ $name }}</x-ui.chip>
        @endforeach
    </nav>

    <section class="ui-inbox-overview" aria-labelledby="averages-title">
        <h2 class="ui-record__title" id="averages-title">{{ __($F.'averages_title') }}</h2>
        <div class="ui-tiles ui-feedback-tiles">
            @foreach ($dimensions as $d)
                <x-ui.stat-tile :label="__($F.'dimensions.'.$d)" :value="$averages[$d]['avg'] !== null ? __($F.'avg', ['avg' => number_format($averages[$d]['avg'], 1)]) : __($F.'no_ratings')"
                    :hint="__($F.'n', ['n' => $averages[$d]['n']])" :href="$link(['page' => null]).'#comments'" />
            @endforeach
        </div>
    </section>

    @if (count($branches) > 1 && $filters['branch'] === null)
        <section class="ui-inbox-overview" aria-labelledby="compare-caption">
            <x-ui.table id="compare" :caption="__($F.'compare_title')" stack row-header="dimension"
                :columns="array_merge([['key' => 'dimension', 'label' => '']], collect($branches)->map(fn ($name, $id) => ['key' => 'b'.$id, 'label' => $name, 'numeric' => true])->values()->all())"
                :rows="collect($dimensions)->map(fn ($d) => ['dimension' => __($F.'dimensions.'.$d)] + collect($branches)->mapWithKeys(fn ($name, $id) => ['b'.$id => $cell($byBranch[$id][$d])])->all())->all()" />
        </section>
    @endif

    <section class="ui-inbox-overview" aria-labelledby="weekly-caption">
        <x-ui.table id="weekly" :caption="__($F.'weekly_title')" stack="wide" row-header="week"
            :columns="array_merge([['key' => 'week', 'label' => __($F.'week')]], collect($dimensions)->map(fn ($d) => ['key' => $d, 'label' => __($F.'dimensions.'.$d), 'numeric' => true])->all())"
            :rows="collect($weekly)->map(fn ($w) => ['week' => $range($w)] + collect($dimensions)->mapWithKeys(fn ($d) => [$d => $cell($w['stats'][$d])])->all())->all()" />
    </section>

    <section class="ui-inbox-overview" aria-labelledby="topics-title">
        <h2 class="ui-record__title" id="topics-title">{{ __($F.'topics_title') }}</h2>
        @if ($topics === [])
            <p class="ui-note">{{ __($F.'topics_none') }}</p>
        @else
            <p class="ui-note">{{ __($F.'topics_warning') }}</p>
            <ul class="ui-record__lines" role="list">
                @foreach ($topics as $topic)
                    <li>{{ $topic['tag'] }} · <bdi>{{ $topic['n'] }}</bdi> <x-ui.badge>{{ __($F.'sources.'.($topic['source'] === 'ai' ? 'ai' : 'rule')) }}</x-ui.badge></li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="ui-inbox-overview" id="comments" aria-labelledby="comments-title">
        <h2 class="ui-record__title" id="comments-title">{{ __($F.'comments_title') }} <bdi>({{ $total }})</bdi></h2>
        <nav class="ui-filter-bar" aria-labelledby="comments-title">
            <x-ui.chip :href="$link(['show' => null, 'page' => null]).'#comments'" :current="! $filters['archived']">{{ __($F.'show_current') }}</x-ui.chip>
            <x-ui.chip :href="$link(['show' => 'archived', 'page' => null]).'#comments'" :current="$filters['archived']">{{ __($F.'show_archived') }}</x-ui.chip>
            <x-ui.chip :href="$link(['low' => $filters['low'] ? null : '1', 'page' => null]).'#comments'" :current="$filters['low']">{{ __($F.'low_only') }}</x-ui.chip>
        </nav>
        @if ($comments === [])
            <p class="ui-note">{{ __($F.'comments_none') }}</p>
        @else
            <ul class="ui-record__notes" role="list">
                @foreach ($comments as $item)
                    <li class="ui-record__section">
                        <p class="ui-record__status">
                            <x-ui.badge :variant="$item->rating_overall <= 2 ? 'warning' : 'neutral'">{{ __($F.'overall', ['n' => $item->rating_overall]) }}</x-ui.badge>
                            <span class="ui-note">{{ $branches[$item->branch_id] ?? '' }} · <bdi>{{ $item->submitted_at->timezone('Asia/Amman')->format('Y-m-d H:i') }}</bdi></span>
                        </p>
                        <p class="ui-record__text" lang="{{ $item->locale }}" dir="auto">{{ $item->comment }}</p>
                        <div class="ui-record__archive">
                            <form method="post" action="{{ route($item->archived_at === null ? 'dashboard.feedback.archive' : 'dashboard.feedback.restore', $item) }}">
                                @csrf
                                <x-ui.button type="submit" size="sm" variant="outline" :icon="$item->archived_at === null ? 'inbox' : 'rotate-cw'">{{ __($F.($item->archived_at === null ? 'archive' : 'restore')) }}</x-ui.button>
                            </form>
                        </div>
                        <x-ui.disclosure :summary="__($F.'redact')">
                            <form class="ui-record__form" method="post" action="{{ route('dashboard.feedback.redact', $item) }}">
                                @csrf
                                @method('PUT')
                                <p class="ui-note">{{ __($F.'redact_hint') }}</p>
                                <x-ui.field :label="__($F.'comments_title')" :for="'comment-'.$item->id">
                                    <x-ui.textarea :id="'comment-'.$item->id" name="comment" rows="3" maxlength="1000" :value="$item->comment" dir="auto" />
                                </x-ui.field>
                                <x-ui.button type="submit" size="sm">{{ __($F.'redact_save') }}</x-ui.button>
                            </form>
                        </x-ui.disclosure>
                    </li>
                @endforeach
            </ul>
            <x-ui.pagination :current="$current" :total="$last" :url="fn (int $page) => $link(['page' => $page]).'#comments'" />
        @endif
    </section>
@endsection
