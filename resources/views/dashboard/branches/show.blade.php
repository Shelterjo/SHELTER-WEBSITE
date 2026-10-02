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

    @php
        $B = 'dashboard.branch.';
        $detailsBag = $errors->getBag('details');
        $dv = fn (string $field, $value) => $detailsBag->any() ? old($field) : $value;
    @endphp
    <section class="ui-record__section" id="details" aria-labelledby="details-title">
        <h2 id="details-title" class="ui-record__title">{{ __($B.'title') }}</h2>
        <p class="ui-note">{{ __($B.'help') }}</p>
        @if ($detailsBag->any())
            <x-ui.error-summary :errors="$detailsBag" :title="__('dashboard.pages.errors.summary')" id="details-errors" />
        @endif
        <form class="ui-record__form" method="post" action="{{ route('dashboard.branches.details', $branch) }}">
            @csrf
            @method('PUT')
            <div class="ui-bilingual">
                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                    <div class="ui-bilingual__column">
                        <x-ui.field :label="__($B.'name_'.$locale)" :for="'name_'.$locale" :error="$detailsBag->first('name_'.$locale)">
                            <x-ui.input :id="'name_'.$locale" :name="'name_'.$locale" maxlength="120" :lang="$locale" :dir="$dir" :value="$dv('name_'.$locale, $branch->{'name_'.$locale})" />
                        </x-ui.field>
                        <x-ui.field :label="__($B.'address_'.$locale)" :for="'address_'.$locale" :hint="__($B.'address_hint')" :error="$detailsBag->first('address_'.$locale)" optional>
                            <x-ui.textarea :id="'address_'.$locale" :name="'address_'.$locale" rows="2" maxlength="300" :lang="$locale" :dir="$dir" :value="$dv('address_'.$locale, $branch->{'address_'.$locale})" />
                        </x-ui.field>
                    </div>
                @endforeach
            </div>
            <x-ui.field :label="__($B.'maps_url')" for="maps_url" :hint="__($B.'maps_hint')" :error="$detailsBag->first('maps_url')" optional>
                <x-ui.input type="url" id="maps_url" name="maps_url" dir="ltr" maxlength="500" inputmode="url" :value="$dv('maps_url', $branch->maps_url)" />
            </x-ui.field>
            <div class="ui-editor__pair">
                <x-ui.field :label="__($B.'latitude')" for="latitude" :hint="__($B.'coordinates_hint')" :error="$detailsBag->first('latitude')" optional>
                    <x-ui.input id="latitude" name="latitude" dir="ltr" inputmode="decimal" maxlength="14" :value="$dv('latitude', $branch->latitude)" />
                </x-ui.field>
                <x-ui.field :label="__($B.'longitude')" for="longitude" :error="$detailsBag->first('longitude')" optional>
                    <x-ui.input id="longitude" name="longitude" dir="ltr" inputmode="decimal" maxlength="14" :value="$dv('longitude', $branch->longitude)" />
                </x-ui.field>
            </div>
            @php $public = (string) $dv('is_public', $branch->is_public ? '1' : '0'); @endphp
            <x-ui.fieldset :legend="__($B.'is_public')" id="is_public">
                <div class="ui-editor__options">
                    <x-ui.radio :label="__($B.'shown')" name="is_public" value="1" id="is_public-1" :checked="$public !== '0'" />
                    <x-ui.radio :label="__($B.'hidden')" name="is_public" value="0" id="is_public-0" :checked="$public === '0'" />
                </div>
            </x-ui.fieldset>
            @foreach ($attributes as $group => $rows)
                <x-ui.fieldset :legend="__($B.'groups.'.$group)" :id="'attr-'.$group">
                    @foreach ($rows as $attribute)
                        @php
                            $name = 'attributes['.$group.']['.$attribute->key.']';
                            $choice = (string) $dv('attributes.'.$group.'.'.$attribute->key, $attribute->value === null ? 'unknown' : ($attribute->value ? 'yes' : 'no'));
                            $id = 'attr-'.$group.'-'.str_replace('_', '-', $attribute->key);
                        @endphp
                        <div class="ui-live-item">
                            <span>{{ __('site.attributes.'.$group.'.'.$attribute->key) }}</span>
                            <div class="ui-editor__options" role="radiogroup" aria-label="{{ __('site.attributes.'.$group.'.'.$attribute->key) }}">
                                @foreach (['yes', 'no', 'unknown'] as $option)
                                    <x-ui.radio :label="__($B.'answers.'.$option)" :name="$name" :value="$option" :id="$id.'-'.$option" :checked="$choice === $option" />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </x-ui.fieldset>
            @endforeach
            <x-ui.field :label="__($B.'reason')" for="details-reason" :hint="__('dashboard.settings.reason_hint')" optional>
                <x-ui.input id="details-reason" name="reason" maxlength="300" />
            </x-ui.field>
            <x-ui.button type="submit">{{ __($B.'save') }}</x-ui.button>
        </form>
    </section>

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
