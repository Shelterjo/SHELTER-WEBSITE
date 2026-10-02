@extends('layouts.dashboard')

@section('title', __('dashboard.menu.season.title'))

@section('content')
    {{--
        The seasonal section (Menu IA §10, CMS-009, MENU-044, F-17): what customers see now, the Owner's choice (by
        its dates · shown now · hidden now), its dates (Amman days), its names and its items. Ending or hiding it never
        deletes an item. No date is ever filled in for the Owner (MENU-042).
    --}}
    @php
        $S = 'dashboard.menu.season.';
        $date = fn ($d) => $d?->format('Y-m-d');
    @endphp
    <x-ui.page-header :title="__($S.'title')" :description="__($S.'description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.menu.index')">{{ __('dashboard.menu.back_to_menu') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($seasons === [])
        <x-ui.empty-state :title="__($S.'none')" icon="coffee" />
    @endif
    <div class="ui-menu-item">
        @foreach ($seasons as $s)
            @php
                $c = $s['category'];
                $bag = $errors->getBag('season'.$c->id);
                $v = fn (string $field, $value) => $bag->any() ? old($field) : $value;
                $mode = (string) $v('mode', $s['mode']);
                $shown = in_array($s['state'], ['shown', 'live'], true);
            @endphp
            <section class="ui-record__section" id="season-{{ $c->id }}" aria-labelledby="season-title-{{ $c->id }}">
                <h2 id="season-title-{{ $c->id }}" class="ui-record__title"><span lang="en" dir="ltr">{{ $c->name_en }}</span></h2>
                <p class="ui-record__status" data-season-state="{{ $s['state'] }}">
                    <span>{{ __($S.'now') }}:</span>
                    <x-ui.badge :variant="$shown ? 'success' : 'neutral'" :icon="$shown ? 'circle-check' : 'pause'">{{ __($S.'states.'.$s['state']) }}</x-ui.badge>
                    <span>{{ $c->season_starts_on !== null || $c->season_ends_on !== null ? __($S.'from_to', ['from' => $date($c->season_starts_on) ?? '—', 'to' => $date($c->season_ends_on) ?? '—']) : __($S.'no_dates') }}</span>
                </p>
                @if ($bag->any())
                    <x-ui.error-summary :errors="collect($bag->getMessages())->mapWithKeys(fn ($m, $k) => [$k.'-'.$c->id => $m])->all()" :title="__('dashboard.pages.errors.summary')" :id="'season-errors-'.$c->id" />
                @endif
                <form class="ui-record__form" method="post" action="{{ route('dashboard.menu.season.save', $c) }}">
                    @csrf
                    @method('PUT')
                    <x-ui.fieldset :legend="__($S.'mode')" :id="'mode-'.$c->id" :error="$bag->first('mode')">
                        @foreach (\App\Services\Menu\MenuSeason::MODES as $option)
                            <x-ui.radio :label="__($S.'modes.'.$option)" name="mode" :value="$option" :id="'mode-'.$option.'-'.$c->id" :checked="$mode === $option" />
                        @endforeach
                    </x-ui.fieldset>
                    <div class="ui-editor__pair">
                        <x-ui.field :label="__($S.'starts_on')" :for="'starts_on-'.$c->id" :hint="__($S.'dates_hint')" :error="$bag->first('starts_on')" optional>
                            <x-ui.input type="date" :id="'starts_on-'.$c->id" name="starts_on" :value="$v('starts_on', $date($c->season_starts_on))" />
                        </x-ui.field>
                        <x-ui.field :label="__($S.'ends_on')" :for="'ends_on-'.$c->id" :error="$bag->first('ends_on')" optional>
                            <x-ui.input type="date" :id="'ends_on-'.$c->id" name="ends_on" :value="$v('ends_on', $date($c->season_ends_on))" />
                        </x-ui.field>
                    </div>
                    <x-ui.field :label="__($S.'name_en')" :for="'name_en-'.$c->id" :error="$bag->first('name_en')">
                        <x-ui.input :id="'name_en-'.$c->id" name="name_en" lang="en" dir="ltr" maxlength="120" :value="$v('name_en', $c->name_en)" />
                    </x-ui.field>
                    <x-ui.field :label="__($S.'name_ar')" :for="'name_ar-'.$c->id" :error="$bag->first('name_ar')" optional>
                        <x-ui.input :id="'name_ar-'.$c->id" name="name_ar" lang="ar" dir="rtl" maxlength="120" :value="$v('name_ar', $c->name_ar ?? $c->suggested_name_ar)" />
                    </x-ui.field>
                    <x-ui.checkbox :label="__($S.'approve_ar')" name="approve_ar" value="1" :id="'approve_ar-'.$c->id" :hint="__('dashboard.menu.fields.approve_hint')" :checked="$bag->any() ? (bool) old('approve_ar') : $s['approved']" />
                    <x-ui.field :label="__($S.'reason')" :for="'reason-'.$c->id" :hint="__($S.'reason_hint')" optional>
                        <x-ui.input :id="'reason-'.$c->id" name="reason" maxlength="300" :value="$v('reason', null)" />
                    </x-ui.field>
                    <x-ui.button type="submit">{{ __($S.'save') }}</x-ui.button>
                </form>

                <h3 class="ui-record__subtitle">{{ __($S.'items_title') }}</h3>
                <p class="ui-note">{{ __($S.'items_hint') }}</p>
                @if ($s['products']->isEmpty())
                    <p class="ui-note">{{ __($S.'items_empty') }}</p>
                @else
                    <ul class="ui-record__lines ui-menu-item__lines" role="list">
                        @foreach ($s['products'] as $p)
                            <li class="ui-live-item">
                                <a href="{{ route('dashboard.menu.show', $p) }}" lang="en" dir="ltr">{{ $p->display_name_en }}</a>
                                @if ($p->publish_status === \App\Enums\PublishStatus::Archived)
                                    <x-ui.badge variant="neutral">{{ __('dashboard.menu.visibility.hidden_badge') }}</x-ui.badge>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
                <x-ui.button variant="secondary" :href="route('dashboard.menu.create', ['category' => $c->id])">{{ __($S.'add_item') }}</x-ui.button>
            </section>
        @endforeach
    </div>
@endsection
