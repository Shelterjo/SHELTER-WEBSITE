@extends('layouts.dashboard')

@section('title', __('dashboard.history.title'))

@section('content')
    {{--
        Change history (App\Services\Dashboard\ChangeHistory): newest first — the area and the item, who, when (Amman
        time), the reason, then each field from → to. Area and period in one small form (works without JavaScript);
        an item's own history comes from its screen (?item=type:id) with a link to its versions. Paginated.
    --}}
    @php
        $H = 'dashboard.history.';
        $link = fn (array $changes): string => route('dashboard.history', array_filter(array_merge([
            'area' => $filters['area'], 'period' => $filters['period'] !== 'all' ? $filters['period'] : null,
            'item' => $filters['item'] !== null ? $filters['item']['type'].':'.$filters['item']['id'] : null,
        ], $changes), fn ($v) => $v !== null && $v !== ''));
        $areas = ['' => __($H.'all_areas')] + collect(\App\Services\Dashboard\ChangeHistory::AREAS)->keys()->mapWithKeys(fn (string $a): array => [$a => __($H.'areas.'.$a)])->all();
        $periods = collect(\App\Services\Dashboard\ChangeHistory::PERIODS)->keys()->mapWithKeys(fn (string $p): array => [$p => __($H.'periods.'.$p)])->all();
    @endphp
    <x-ui.page-header :title="__($H.'title')" :description="__($H.'description')" />

    @if ($item !== null)
        <div class="ui-record__section ui-record__section--status ui-history__entry">
            <p class="ui-record__status"><strong>{{ __($H.'item_title', ['item' => $item['name']]) }}</strong></p>
            <div class="ui-cluster">
                @if ($item['versions'] !== null)
                    <x-ui.button size="sm" variant="secondary" :href="$item['versions']" icon="rotate-cw">{{ __($H.'versions_link') }}</x-ui.button>
                @endif
                <x-ui.button size="sm" variant="ghost" :href="$link(['item' => null, 'page' => null])">{{ __($H.'all_items') }}</x-ui.button>
            </div>
        </div>
    @endif

    <form class="ui-inbox-filters" method="get" action="{{ route('dashboard.history') }}" aria-label="{{ __($H.'filters') }}">
        @if ($filters['item'] !== null)
            <input type="hidden" name="item" value="{{ $filters['item']['type'] }}:{{ $filters['item']['id'] }}">
        @endif
        <div class="ui-inbox-filters__main">
            <x-ui.field :label="__($H.'area')" for="area">
                <x-ui.select id="area" name="area" :options="$areas" :selected="$filters['area'] ?? ''" />
            </x-ui.field>
            <x-ui.field :label="__($H.'period')" for="period">
                <x-ui.select id="period" name="period" :options="$periods" :selected="$filters['period']" />
            </x-ui.field>
        </div>
        <div class="ui-inbox-filters__actions">
            <x-ui.button type="submit" icon="search">{{ __($H.'show') }}</x-ui.button>
            @if ($filters['area'] !== null || $filters['period'] !== 'all')
                <x-ui.button variant="ghost" :href="$link(['area' => null, 'period' => null, 'page' => null])">{{ __($H.'clear') }}</x-ui.button>
            @endif
        </div>
    </form>

    <p class="ui-note" role="status">{{ trans_choice($H.'count', $total, ['count' => $total]) }}@if ($filters['area'] === null) · {{ __($H.'access_note') }}@endif</p>

    @if ($entries === [])
        <x-ui.empty-state :title="__($H.'empty_title')">{{ __($H.'empty_text') }}</x-ui.empty-state>
    @else
        <ul class="ui-record__notes" role="list">
            @foreach ($entries as $entry)
                <li class="ui-record__section ui-history__entry" id="e{{ $entry['id'] }}">
                    <p class="ui-record__status">
                        <x-ui.badge :variant="$entry['restored'] !== null ? 'info' : 'neutral'">{{ __($H.'areas.'.$entry['area']) }}</x-ui.badge>
                        <strong>{{ $entry['action'] }}</strong>
                        @if ($entry['item'] !== null)
                            <span>— <bdi>{{ $entry['item'] }}</bdi></span>
                        @endif
                    </p>
                    <p class="ui-note">{{ $entry['who'] }} · <bdi dir="ltr">{{ $entry['when'] }}</bdi></p>
                    @if ($entry['restored'] !== null && $entry['reason'] === null)
                        <p>{{ __($H.'restored_line', ['version' => $entry['restored']]) }}</p>
                    @endif
                    @if ($entry['reason'] !== null)
                        <p class="ui-record__text">{{ __($H.'reason', ['reason' => $entry['reason']]) }}</p>
                    @endif
                    @if ($entry['lines'] !== [])
                        <dl class="ui-facts">
                            @foreach ($entry['lines'] as $line)
                                <div>
                                    <dt><bdi>{{ $line['field'] }}</bdi></dt>
                                    @if ($line['from'] !== null)
                                        <dd>{{ __($H.'from') }} <bdi>{{ $line['from'] }}</bdi></dd>
                                    @endif
                                    @if ($line['to'] !== null)
                                        <dd>{{ __($line['from'] !== null ? $H.'to' : $H.'value') }} <bdi>{{ $line['to'] }}</bdi></dd>
                                    @endif
                                    @if ($line['versions'] !== null)
                                        <dd><a href="{{ $line['versions'] }}">{{ __($H.'versions_link') }}</a></dd>
                                    @endif
                                </div>
                            @endforeach
                        </dl>
                    @endif
                    @if ($entry['versions'] !== null)
                        <div class="ui-cluster">
                            <x-ui.button size="sm" variant="outline" :href="$entry['versions']" icon="rotate-cw">{{ __($H.'versions_link') }}</x-ui.button>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
        <x-ui.pagination :current="$current" :total="$last" :url="fn (int $page) => $link(['page' => $page])" />
    @endif
@endsection
