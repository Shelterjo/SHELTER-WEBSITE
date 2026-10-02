@extends('layouts.dashboard')

@section('title', $name)

@section('content')
    {{--
        One branch's hours (HoursEditor): the regular week (preview → reason → publish, BRANCH-010) and the exceptions
        (special hours, holidays, temporary and emergency closures — CMS-013). The preview uses the site's own week rows.
    --}}
    @php
        $H = 'dashboard.hours.';
        $exceptionErrors = $errors->getBag('exception');
        $describe = function ($e) use ($H): string {
            $what = $e->is_closed ? __($H.'closed_all_day') : \App\Support\LocalTime::clock(substr((string) $e->opens_at, 0, 5), app()->getLocale()).' – '.\App\Support\LocalTime::clock(substr((string) $e->closes_at, 0, 5), app()->getLocale());

            return __($H.'range', ['from' => $e->starts_on->format('Y-m-d'), 'to' => $e->ends_on->format('Y-m-d')]).' · '.$what;
        };
    @endphp
    <x-ui.page-header :title="$name">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.branches.index')">{{ __('dashboard.branches.title') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <p class="ui-record__status ui-branch-state">
        @if ($state === null)
            <x-ui.badge icon="circle-alert">{{ __('dashboard.branches.unknown_now') }}</x-ui.badge>
        @elseif ($state->isOpen)
            <x-ui.badge variant="success" icon="circle-check">{{ __('dashboard.branches.open_now') }}</x-ui.badge>
        @else
            <x-ui.badge icon="clock">{{ __('dashboard.branches.closed_now') }}</x-ui.badge>
        @endif
    </p>

    @if ($hoursErrors !== [])
        <x-ui.error-summary :errors="collect($hoursErrors)->mapWithKeys(fn ($m, $k) => [(string) preg_replace('/^days\.(\d+)$/', 'd$1-opens', $k) => $m])->all()" :title="__('dashboard.pages.errors.summary')" />
    @endif

    <div class="ui-record">
        <form class="ui-record__section" method="post" action="{{ route('dashboard.branches.hours', $branch) }}" aria-labelledby="weekly-title">
            @csrf
            <h2 class="ui-record__title" id="weekly-title">{{ __($H.'weekly_title') }}</h2>
            <p class="ui-note">{{ __($H.'weekly_help') }}</p>
            <div class="ui-week">
                @foreach ($days as $day => $value)
                    <fieldset @class(['ui-week__day', 'ui-week__day--error' => isset($hoursErrors['days.'.$day])]) id="d{{ $day }}">
                        <legend class="ui-week__name">{{ \App\Support\LocalTime::weekday($day, app()->getLocale()) }}</legend>
                        <x-ui.checkbox :label="__($H.'open')" :name="'days['.$day.'][open]'" value="1" :id="'d'.$day.'-open'" :checked="$value['open']" />
                        <div class="ui-week__times">
                            <x-ui.field :label="__($H.'opens')" :for="'d'.$day.'-opens'" :error="$hoursErrors['days.'.$day] ?? null">
                                <x-ui.input type="time" :id="'d'.$day.'-opens'" :name="'days['.$day.'][opens]'" :value="$value['opens']" step="300" />
                            </x-ui.field>
                            <x-ui.field :label="__($H.'closes')" :for="'d'.$day.'-closes'">
                                <x-ui.input type="time" :id="'d'.$day.'-closes'" :name="'days['.$day.'][closes]'" :value="$value['closes']" step="300" />
                            </x-ui.field>
                        </div>
                    </fieldset>
                @endforeach
            </div>
            <div class="ui-record__archive">
                <x-ui.button type="submit" name="action" value="preview" icon="search" :variant="$preview === null ? 'primary' : 'outline'">{{ __($H.'preview') }}</x-ui.button>
            </div>

            @if ($preview !== null)
                <section class="ui-preview" id="preview" aria-labelledby="preview-title">
                    <h3 class="ui-record__subtitle" id="preview-title">{{ __($H.'preview_title') }}</h3>
                    <p class="ui-note">{{ __($H.'preview_help') }}</p>
                    <p class="ui-record__status">
                        <x-ui.badge :variant="$changed === [] ? 'neutral' : 'warning'" icon="circle-alert">{{ __($H.'changed') }}</x-ui.badge>
                        <span>{{ $changed === [] ? __($H.'nothing_changed') : implode('، ', $changed) }}</span>
                    </p>
                    <div class="ui-preview__compare">
                        <div>
                            <p class="ui-bilingual__language">{{ __($H.'preview_title') }}</p>
                            <x-ui.hours-table :rows="$preview" :caption="__($H.'preview_title')" />
                        </div>
                        <div>
                            <p class="ui-bilingual__language">{{ __($H.'now_title') }}</p>
                            <x-ui.hours-table :rows="$current" :caption="__($H.'now_title')" />
                        </div>
                    </div>
                    <input type="hidden" name="previewed" value="{{ $fingerprint }}">
                    <x-ui.field :label="__($H.'reason')" for="reason" :hint="__($H.'reason_hint')" :error="$hoursErrors['reason'] ?? null">
                        <x-ui.input id="reason" name="reason" maxlength="300" :value="old('reason', request('reason'))" />
                    </x-ui.field>
                    <x-ui.button type="submit" name="action" value="publish" icon="circle-check">{{ __($H.'publish') }}</x-ui.button>
                </section>
            @endif
        </form>

        <section class="ui-record__section" id="exceptions" aria-labelledby="exceptions-title">
            <h2 class="ui-record__title" id="exceptions-title">{{ __($H.'exceptions_title') }}</h2>
            <p class="ui-note">{{ __($H.'exceptions_help') }}</p>

            <h3 class="ui-record__subtitle">{{ __($H.'upcoming') }}</h3>
            @if ($upcoming->isEmpty())
                <p class="ui-note">{{ __($H.'none') }}</p>
            @else
                <ul class="ui-record__notes" role="list">
                    @foreach ($upcoming as $e)
                        <li class="ui-record__note" id="x{{ $e->id }}">
                            <p class="ui-record__status">
                                <x-ui.badge :variant="$e->kind->value === 'emergency' ? 'danger' : ($e->is_closed ? 'warning' : 'info')">{{ __($H.'kinds.'.$e->kind->value) }}</x-ui.badge>
                                @if ($e->status->value === 'draft')
                                    <x-ui.badge>{{ __($H.'draft_badge') }}</x-ui.badge>
                                @endif
                                <strong><bdi>{{ $describe($e) }}</bdi></strong>
                            </p>
                            <p class="ui-note">{{ $e->reason_ar }}</p>
                            <x-ui.disclosure :summary="__($H.'edit')">
                                @include('dashboard.branches._exception', ['exception' => $e, 'bag' => $errors->getBag('x'.$e->id), 'prefix' => 'x'.$e->id.'-',
                                    'action' => route('dashboard.branches.exceptions.update', [$branch, $e]), 'method' => 'PUT'])
                            </x-ui.disclosure>
                            <form method="post" action="{{ route('dashboard.branches.exceptions.archive', [$branch, $e]) }}" class="ui-record__archive">
                                @csrf
                                <x-ui.button type="submit" size="sm" variant="outline" icon="circle-x">{{ __($H.'end') }}</x-ui.button>
                                <span class="ui-note">{{ __($H.'end_help') }}</span>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            <h3 class="ui-record__subtitle" id="add-exception">{{ __($H.'add') }}</h3>
            @if ($exceptionErrors->any())
                <x-ui.error-summary :errors="collect($exceptionErrors->getMessages())->mapWithKeys(fn ($m, $k) => ['new-'.$k => $m[0]])->all()" :title="__('dashboard.pages.errors.summary')" id="exception-errors" />
            @endif
            @include('dashboard.branches._exception', ['exception' => null, 'bag' => $exceptionErrors, 'prefix' => 'new-',
                'action' => route('dashboard.branches.exceptions.store', $branch), 'method' => 'POST'])

            @if ($past->isNotEmpty())
                <x-ui.disclosure :summary="__($H.'past')">
                    <ul class="ui-record__lines" role="list">
                        @foreach ($past as $e)
                            <li>{{ __($H.'kinds.'.$e->kind->value) }} · <bdi>{{ $describe($e) }}</bdi></li>
                        @endforeach
                    </ul>
                </x-ui.disclosure>
            @endif
        </section>
    </div>
@endsection
