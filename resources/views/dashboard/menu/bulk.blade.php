@extends('layouts.dashboard')

@section('title', __('dashboard.menu.bulk.confirm_title'))

@section('content')
    {{--
        One clear confirmation for several menu items (MENU-062): what will happen, to which items, and — for a move or
        a branch availability — the section, or the branch, the availability and the reason. Prices never change here.
    --}}
    @php
        $B = 'dashboard.menu.bulk.';
        $bag = $errors->getBag('bulk');
        $ar = app()->getLocale() === 'ar';
    @endphp
    <x-ui.page-header :title="__($B.'confirm_title')" />
    <section class="ui-record__section ui-record__section--status" aria-labelledby="bulk-question">
        <h2 id="bulk-question" class="ui-record__title">{{ trans_choice($B.'question.'.$action, $products->count(), ['count' => $products->count()]) }}</h2>
        @if ($bag->any())
            <x-ui.error-summary :errors="$bag" :title="__('dashboard.pages.errors.summary')" id="bulk-errors" />
        @endif
        <ul class="ui-record__lines" role="list">
            @foreach ($products as $p)
                <li><span lang="en" dir="ltr">{{ $p->display_name_en }}</span> · <bdi>{{ $p->code }}</bdi></li>
            @endforeach
        </ul>
        <form class="ui-record__form" method="post" action="{{ route('dashboard.menu.bulk.apply') }}">
            @csrf
            <input type="hidden" name="action" value="{{ $action }}">
            @foreach ($products as $p)
                <input type="hidden" name="ids[]" value="{{ $p->id }}">
            @endforeach
            @if ($action === 'move')
                <x-ui.field :label="__($B.'target')" for="category" :error="$bag->first('category')">
                    <x-ui.select id="category" name="category" :options="$categories" :selected="old('category')" placeholder />
                </x-ui.field>
            @elseif ($action === 'branch')
                <x-ui.field :label="__($B.'branch')" for="branch">
                    <x-ui.select id="branch" name="branch" :options="$branches" :selected="old('branch')" />
                </x-ui.field>
                <x-ui.fieldset :legend="__($B.'state')" id="state" :error="$bag->first('state')">
                    @foreach (\App\Services\Dashboard\MenuManager::BRANCH_STATES as $option)
                        <x-ui.radio :label="__('dashboard.menu.states.'.$option)" name="state" :value="$option" :id="'state-'.$option" :checked="old('state') === $option" />
                    @endforeach
                </x-ui.fieldset>
                <x-ui.field :label="__('dashboard.menu.fields.reason')" for="reason" :hint="__('dashboard.menu.fields.reason_hint')" :error="$bag->first('reason')">
                    <x-ui.input id="reason" name="reason" maxlength="300" :value="old('reason')" />
                </x-ui.field>
            @endif
            <p class="ui-note">{{ __($B.'no_price') }}</p>
            <div class="ui-record__archive">
                <x-ui.button type="submit">{{ __($B.'confirm') }}</x-ui.button>
                <x-ui.button variant="ghost" :href="$back">{{ __($B.'cancel') }}</x-ui.button>
            </div>
        </form>
    </section>
@endsection
