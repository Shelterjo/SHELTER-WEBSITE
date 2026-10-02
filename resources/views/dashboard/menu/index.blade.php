@extends('layouts.dashboard')

@section('title', __('dashboard.menu.title'))

@section('content')
    {{--
        Business data → Menu: one category at a time (or a search across all), each item with its name in both
        languages, its price now and whether a branch differs; one click opens the item. Arabic names that still await
        the Owner's review are counted per category, with a link to review them all at once (D-137).
    --}}
    @php
        $M = 'dashboard.menu.';
        $ar = app()->getLocale() === 'ar';
        $catName = fn ($c): string => ($ar && \App\Enums\NameStatus::fromInventory((string) $c->name_ar_status) === \App\Enums\NameStatus::Approved && filled($c->name_ar)) ? (string) $c->name_ar : (string) $c->name_en;
    @endphp
    <x-ui.page-header :title="__($M.'title')" :description="__($M.'description')" />

    <form class="ui-inbox-filters" method="get" action="{{ route('dashboard.menu.index') }}" role="search">
        <div class="ui-inbox-filters__main">
            <x-ui.field :label="__($M.'search')" for="q">
                <x-ui.input type="search" id="q" name="q" :value="$q" maxlength="100" autocomplete="off" />
            </x-ui.field>
        </div>
        <div class="ui-inbox-filters__actions">
            <x-ui.button type="submit" icon="search">{{ __($M.'search_button') }}</x-ui.button>
        </div>
    </form>

    <nav class="ui-filter-bar" aria-label="{{ __($M.'title') }}">
        @foreach ($categories as $c)
            <x-ui.chip :href="route('dashboard.menu.index', ['category' => $c->code])" :current="$category?->id === $c->id"><span @if (! $ar || $catName($c) === $c->name_en) lang="en" dir="ltr" @endif>{{ $catName($c) }}</span> <bdi>({{ $c->shown_count }})</bdi></x-ui.chip>
        @endforeach
    </nav>

    @if ($category !== null)
        @php $waiting = $pending[$category->id] ?? 0; @endphp
        <p class="ui-record__status">
            <x-ui.badge :variant="$waiting > 0 ? 'warning' : 'success'" :icon="$waiting > 0 ? 'hand' : 'circle-check'">{{ trans_choice($M.'pending_names', $waiting, ['count' => $waiting]) }}</x-ui.badge>
            @if ($waiting > 0)
                <a href="{{ route('dashboard.menu.review', $category) }}">{{ __($M.'review_names') }}</a>
            @endif
        </p>
    @endif

    @if ($rows === [])
        <x-ui.empty-state :title="__($M.'empty')" icon="coffee" />
    @else
        <x-ui.table :caption="$category !== null ? $catName($category) : __($M.'results', ['q' => $q])" stack>
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col" role="columnheader">{{ __($M.'columns.item') }}</th>
                    <th scope="col" role="columnheader" class="ui-table__numeric">{{ __($M.'columns.price') }}</th>
                    <th scope="col" role="columnheader">{{ __($M.'columns.branches') }}</th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                @foreach ($rows as $row)
                    @php $p = $row['product']; @endphp
                    <tr role="row">
                        <th scope="row" role="rowheader" data-label="{{ __($M.'columns.item') }}">
                            <a href="{{ route('dashboard.menu.show', $p) }}" lang="en" dir="ltr">{{ $p->display_name_en }}</a>
                            <span class="ui-menu-table__ar">
                                @if ($row['arabic'] !== null)
                                    <span lang="ar" dir="rtl">{{ $row['arabic'] }}</span>
                                @else
                                    <x-ui.badge variant="warning" icon="hand">{{ __($M.'ar_pending') }}</x-ui.badge>
                                @endif
                            </span>
                            <span class="ui-menu-table__code"><bdi>{{ $p->code }}</bdi></span>
                            @if ($row['hidden'])
                                <span class="ui-menu-table__ar"><x-ui.badge icon="ban">{{ __($M.'visibility.hidden_badge') }}</x-ui.badge></span>
                            @endif
                        </th>
                        <td role="cell" data-label="{{ __($M.'columns.price') }}" class="ui-table__numeric">
                            @if ($row['price'] !== null)
                                <x-ui.price :fils="$row['price']" />
                            @else
                                —
                            @endif
                        </td>
                        <td role="cell" data-label="{{ __($M.'columns.branches') }}">
                            {{ $row['branches'] === 0 ? __($M.'same_everywhere') : trans_choice($M.'branch_differs', $row['branches'], ['count' => $row['branches']]) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    @endif
@endsection
