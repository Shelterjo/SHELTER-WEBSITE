@extends('layouts.dashboard')

@section('title', __('dashboard.seo.title'))

@section('content')
    {{--
        Google visibility (M57 §48): what search engines and map apps can read today and what is missing, in plain words,
        each gap linked to the screen that completes it. Read-only; Search Console is not connected yet.
    --}}
    @php $S = 'dashboard.seo.'; @endphp
    <x-ui.page-header :title="__($S.'title')" :description="__($S.'description')" />

    <section class="ui-record__section" aria-labelledby="seo-indexing">
        <h2 id="seo-indexing" class="ui-record__title">{{ __($S.'indexing.title') }}</h2>
        <p class="ui-record__status">
            @if ($indexable)
                <x-ui.badge variant="success" icon="circle-check">{{ __($S.'indexing.on_badge') }}</x-ui.badge>
                <span>{{ __($S.'indexing.on') }}</span>
            @else
                <x-ui.badge variant="info" icon="info">{{ __($S.'indexing.off_badge') }}</x-ui.badge>
                <span>{{ __($S.'indexing.off') }}</span>
            @endif
        </p>
        <h3 class="ui-record__title">{{ __($S.'console.title') }}</h3>
        <p class="ui-note">{{ __($S.'console.text') }}</p>
    </section>

    <section class="ui-record__section" aria-labelledby="seo-branches">
        <h2 id="seo-branches" class="ui-record__title">{{ __($S.'branches_title') }}</h2>
        <p class="ui-note">{{ __($S.'branches_help') }}</p>
        @foreach ($branches as $row)
            <h3 class="ui-record__title">{{ $row['name'] }}</h3>
            <ul class="ui-stack ui-stack--sm" role="list">
                @foreach ($row['checks'] as $check => $ok)
                    <li class="ui-record__status ui-record__status--check">
                        @if ($ok)
                            <x-ui.badge variant="success" icon="circle-check">{{ __($S.'ok') }}</x-ui.badge>
                        @else
                            <x-ui.badge variant="warning" icon="triangle-alert">{{ __($S.'missing') }}</x-ui.badge>
                        @endif
                        <span>{{ __($S.'checks.'.$check) }}</span>
                    </li>
                @endforeach
            </ul>
            @if (in_array(false, $row['checks'], true))
                <p><x-ui.button variant="secondary" size="sm" :href="route('dashboard.branches.show', $row['branch'])" icon-end="arrow-right">{{ __($S.'fix') }}</x-ui.button></p>
            @endif
        @endforeach
    </section>

    <section class="ui-record__section" aria-labelledby="seo-texts">
        <h2 id="seo-texts" class="ui-record__title">{{ __($S.'texts_title') }}</h2>
        <p class="ui-note">{{ __($S.'texts_help', ['title' => \App\Services\Dashboard\SeoHealth::TITLE_MAX, 'description' => \App\Services\Dashboard\SeoHealth::DESCRIPTION_MAX]) }}</p>
        <x-ui.table :caption="__($S.'texts_title')" stack="wide">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col" role="columnheader">{{ __($S.'columns.page') }}</th>
                    <th scope="col" role="columnheader">{{ __($S.'columns.title') }}</th>
                    <th scope="col" role="columnheader">{{ __($S.'columns.description') }}</th>
                    <th scope="col" role="columnheader">{{ __($S.'columns.state') }}</th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                @foreach ($texts as $row)
                    <tr role="row">
                        <th scope="row" role="rowheader" data-label="{{ __($S.'columns.page') }}">
                            <a class="ui-action-link" href="{{ route('dashboard.texts.index', ['page' => $row['group']]) }}">{{ __($S.'pages.'.$row['page']) }}</a>
                            <span class="ui-note">{{ __($S.'languages.'.$row['locale']) }}</span>
                        </th>
                        <td role="cell" data-label="{{ __($S.'columns.title') }}"><span lang="{{ $row['locale'] }}" dir="{{ $row['locale'] === 'ar' ? 'rtl' : 'ltr' }}">{{ $row['title'] }}</span></td>
                        <td role="cell" data-label="{{ __($S.'columns.description') }}"><span lang="{{ $row['locale'] }}" dir="{{ $row['locale'] === 'ar' ? 'rtl' : 'ltr' }}">{{ $row['description'] }}</span></td>
                        <td role="cell" data-label="{{ __($S.'columns.state') }}">
                            @forelse ($row['issues'] as $issue)
                                <x-ui.badge variant="warning" icon="triangle-alert">{{ __($S.'issues.'.$issue) }}</x-ui.badge>
                            @empty
                                <x-ui.badge variant="success" icon="circle-check">{{ __($S.'fine') }}</x-ui.badge>
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    </section>

    <section class="ui-record__section" aria-labelledby="seo-menu">
        <h2 id="seo-menu" class="ui-record__title">{{ __($S.'menu_title') }}</h2>
        <p class="ui-note">{{ __($S.'menu_help') }}</p>
        <ul class="ui-stack ui-stack--sm" role="list">
            <li class="ui-record__status">
                <x-ui.badge :variant="$arabicMenu['sections_missing'] > 0 ? 'warning' : 'success'" :icon="$arabicMenu['sections_missing'] > 0 ? 'triangle-alert' : 'circle-check'">{{ $arabicMenu['sections_missing'] > 0 ? __($S.'missing') : __($S.'ok') }}</x-ui.badge>
                <span>{{ __($S.'menu_sections', ['missing' => $arabicMenu['sections_missing'], 'total' => $arabicMenu['sections']]) }}</span>
            </li>
            <li class="ui-record__status">
                <x-ui.badge :variant="$arabicMenu['items_missing'] > 0 ? 'warning' : 'success'" :icon="$arabicMenu['items_missing'] > 0 ? 'triangle-alert' : 'circle-check'">{{ $arabicMenu['items_missing'] > 0 ? __($S.'missing') : __($S.'ok') }}</x-ui.badge>
                <span>{{ __($S.'menu_items', ['missing' => $arabicMenu['items_missing'], 'total' => $arabicMenu['items']]) }}</span>
            </li>
        </ul>
        @if ($arabicMenu['sections_missing'] > 0 || $arabicMenu['items_missing'] > 0)
            <p><x-ui.button variant="secondary" size="sm" :href="route('dashboard.menu.index')" icon-end="arrow-right">{{ __($S.'menu_fix') }}</x-ui.button></p>
        @endif
    </section>
@endsection
