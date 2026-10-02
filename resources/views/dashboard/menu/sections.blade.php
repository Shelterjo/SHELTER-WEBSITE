@extends('layouts.dashboard')

@section('title', __('dashboard.menu.sections.title'))

@section('content')
    {{--
        Menu → Sections (M50, MENU-039, P-01): each section in its order — up / down, its names (Arabic shown once
        approved), shown or hidden — the season (own screen) on top, and a form for a new section at the end.
    --}}
    @php
        $S = 'dashboard.menu.sections.';
        $M = 'dashboard.menu.';
        $newBag = $errors->getBag('new');
    @endphp
    <x-ui.page-header :title="__($S.'title')" :description="__($S.'description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.menu.index')">{{ __($M.'back_to_menu') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="ui-menu-item">
        @foreach ($rows as $row)
            @php
                $c = $row['category'];
                $bag = $errors->getBag('section'.$c->id);
                $v = fn (string $field, $value) => $bag->any() ? old($field) : $value;
                $position = array_search($c->id, $regular, true);
                $hidden = $c->status === \App\Services\Menu\MenuSections::HIDDEN;
            @endphp
            <section class="ui-record__section" id="section-{{ $c->id }}" aria-labelledby="section-title-{{ $c->id }}">
                <div class="ui-live-item">
                    <h2 id="section-title-{{ $c->id }}" class="ui-record__title"><span lang="en" dir="ltr">{{ $c->name_en }}</span></h2>
                    <p class="ui-record__status">
                        <bdi>{{ $c->code }}</bdi>
                        <span>{{ trans_choice($S.'items', $row['items'], ['count' => $row['items']]) }}</span>
                        @if ($c->type === 'seasonal')
                            <x-ui.badge variant="info">{{ __($M.'season.label') }}</x-ui.badge>
                        @elseif ($hidden)
                            <x-ui.badge icon="ban">{{ __($S.'hidden_badge') }}</x-ui.badge>
                        @endif
                        @if ($c->group !== null)
                            <x-ui.badge>{{ __($S.'in_group', ['group' => $c->group->name_ar ?? $c->group->name_en]) }}</x-ui.badge>
                        @endif
                    </p>
                </div>
                @if ($position !== false)
                    <div class="ui-editor__options">
                        @foreach (['up', 'down'] as $direction)
                            <form method="post" action="{{ route('dashboard.menu.sections.move', [$c, $direction]) }}">
                                @csrf
                                <x-ui.button type="submit" size="sm" variant="ghost" :disabled="($direction === 'up' && $position === 0) || ($direction === 'down' && $position === count($regular) - 1)">{{ __('dashboard.requests.view.'.$direction) }}<span class="ui-visually-hidden"> — {{ $c->name_en }}</span></x-ui.button>
                            </form>
                        @endforeach
                    </div>
                @endif
                @if ($bag->any())
                    <x-ui.error-summary :errors="collect($bag->getMessages())->mapWithKeys(fn ($m, $k) => [$k.'-'.$c->id => $m])->all()" :title="__('dashboard.pages.errors.summary')" :id="'section-errors-'.$c->id" />
                @endif
                <x-ui.disclosure :summary="__($S.'edit')" :open="$bag->any()">
                    <form class="ui-record__form" method="post" action="{{ route('dashboard.menu.sections.update', $c) }}">
                        @csrf
                        @method('PUT')
                        <x-ui.field :label="__($M.'fields.name_en')" :for="'name_en-'.$c->id" :error="$bag->first('name_en')">
                            <x-ui.input :id="'name_en-'.$c->id" name="name_en" lang="en" dir="ltr" maxlength="60" :value="$v('name_en', $c->name_en)" />
                        </x-ui.field>
                        <x-ui.field :label="__($M.'fields.name_ar')" :for="'name_ar-'.$c->id" :error="$bag->first('name_ar')" optional>
                            <x-ui.input :id="'name_ar-'.$c->id" name="name_ar" lang="ar" dir="rtl" maxlength="60" :value="$v('name_ar', $c->name_ar ?? $c->suggested_name_ar)" />
                        </x-ui.field>
                        <x-ui.checkbox :label="__($M.'fields.approve_ar')" name="approve_ar" value="1" :id="'approve_ar-'.$c->id" :hint="__($M.'fields.approve_hint')" :checked="$bag->any() ? (bool) old('approve_ar') : $row['approved']" />
                        @if ($c->type === 'seasonal')
                            <p class="ui-note"><a href="{{ route('dashboard.menu.season') }}">{{ __($S.'season_note') }}</a></p>
                        @else
                            @php $visible = (string) $v('visible', $hidden ? '0' : '1'); @endphp
                            <x-ui.fieldset :legend="__($S.'visible')" :id="'visible-'.$c->id">
                                <div class="ui-editor__options">
                                    <x-ui.radio :label="__($S.'shown')" name="visible" value="1" :id="'visible-1-'.$c->id" :checked="$visible !== '0'" />
                                    <x-ui.radio :label="__($S.'hidden')" name="visible" value="0" :id="'visible-0-'.$c->id" :checked="$visible === '0'" />
                                </div>
                            </x-ui.fieldset>
                        @endif
                        <x-ui.button type="submit">{{ __('dashboard.save') }}</x-ui.button>
                    </form>
                </x-ui.disclosure>
            </section>
        @endforeach

        <section class="ui-record__section" id="new-section" aria-labelledby="new-section-title">
            <h2 id="new-section-title" class="ui-record__title">{{ __($S.'new_title') }}</h2>
            <p class="ui-note">{{ __($S.'new_help') }}</p>
            @if ($newBag->any())
                <x-ui.error-summary :errors="collect($newBag->getMessages())->mapWithKeys(fn ($m, $k) => ['new-'.$k => $m])->all()" :title="__('dashboard.pages.errors.summary')" id="new-errors" />
            @endif
            <form class="ui-record__form" method="post" action="{{ route('dashboard.menu.sections.store') }}">
                @csrf
                <x-ui.field :label="__($M.'fields.name_en')" for="new-name_en" :error="$newBag->first('name_en')">
                    <x-ui.input id="new-name_en" name="name_en" lang="en" dir="ltr" maxlength="60" :value="$newBag->any() ? old('name_en') : null" />
                </x-ui.field>
                <x-ui.field :label="__($M.'fields.name_ar')" for="new-name_ar" :hint="__($M.'create.name_ar_hint')" :error="$newBag->first('name_ar')" optional>
                    <x-ui.input id="new-name_ar" name="name_ar" lang="ar" dir="rtl" maxlength="60" :value="$newBag->any() ? old('name_ar') : null" />
                </x-ui.field>
                <x-ui.button type="submit">{{ __($S.'add') }}</x-ui.button>
            </form>
        </section>
    </div>
@endsection
