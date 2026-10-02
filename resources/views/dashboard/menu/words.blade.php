@extends('layouts.dashboard')

@section('title', __('dashboard.menu.words.title'))

@section('content')
    {{--
        The search words in one place (CMS-018): try a search exactly as a customer would (the same engine as the site
        search), every word by item with a link to change it, and — only once anonymous counting is on (PO-019) — the
        searches that found nothing.
    --}}
    @php $W = 'dashboard.menu.words.'; @endphp
    <x-ui.page-header :title="__($W.'title')" :description="__($W.'description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.menu.index')">{{ __('dashboard.menu.back_to_menu') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="ui-menu-item">
        <section class="ui-record__section" aria-labelledby="try-title">
            <h2 id="try-title" class="ui-record__title">{{ __($W.'try') }}</h2>
            <form class="ui-record__form" method="get" action="{{ route('dashboard.menu.words') }}" role="search">
                <x-ui.field :label="__($W.'try')" for="q">
                    <x-ui.input type="search" id="q" name="q" :value="$q" maxlength="60" autocomplete="off" />
                </x-ui.field>
                <x-ui.button type="submit" icon="search">{{ __($W.'try_button') }}</x-ui.button>
            </form>
            @if ($hits !== null)
                <h3 class="ui-record__subtitle">{{ __($W.'found', ['q' => $q]) }}</h3>
                @if ($hits === [])
                    <p class="ui-note">{{ __($W.'none') }}</p>
                @else
                    <ul class="ui-record__lines ui-menu-item__lines" role="list" data-try-results>
                        @foreach ($hits as $hit)
                            <li class="ui-live-item">
                                <span><bdi @if ($hit->titleLang) lang="{{ $hit->titleLang }}" @endif>{{ $hit->title }}</bdi>@if ($hit->meta) · <bdi @if ($hit->metaLang) lang="{{ $hit->metaLang }}" @endif>{{ $hit->meta }}</bdi>@endif</span>
                                <a class="ui-action-link" href="{{ $hit->url }}" target="_blank" rel="noopener">{{ __($W.'open') }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </section>

        <section class="ui-record__section" aria-labelledby="list-title">
            <h2 id="list-title" class="ui-record__title">{{ __($W.'list_title') }}</h2>
            @if ($items->isEmpty())
                <p class="ui-note">{{ __($W.'list_empty') }}</p>
            @else
                <ul class="ui-record__lines ui-menu-item__lines" role="list">
                    @foreach ($items as $item)
                        <li class="ui-live-item">
                            <span>
                                <strong lang="en" dir="ltr">{{ $item['product']?->display_name_en }}</strong>:
                                @foreach ($item['words'] as $word)
                                    <bdi lang="{{ $word->locale }}">{{ $word->value }}</bdi>@if (! $loop->last) · @endif
                                @endforeach
                            </span>
                            @if ($item['product'] !== null)
                                <a class="ui-action-link" href="{{ route('dashboard.menu.show', $item['product']) }}#search-words">{{ __($W.'edit') }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="ui-record__section" aria-labelledby="missed-title">
            <h2 id="missed-title" class="ui-record__title">{{ __($W.'missed_title') }}</h2>
            @if (! $logging)
                <p class="ui-note">{{ __($W.'missed_off') }}</p>
            @elseif ($missed->isEmpty())
                <p class="ui-note">{{ __($W.'missed_empty') }}</p>
            @else
                <ul class="ui-record__lines ui-menu-item__lines" role="list">
                    @foreach ($missed as $row)
                        <li class="ui-live-item">
                            <bdi>{{ $row->query_norm }}</bdi>
                            <span>{{ trans_choice($W.'times', (int) $row->times, ['count' => (int) $row->times]) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
