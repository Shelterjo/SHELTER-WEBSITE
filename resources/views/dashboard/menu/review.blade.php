@extends('layouts.dashboard')

@section('title', __('dashboard.menu.review_title', ['category' => $category->name_en]))

@section('content')
    {{--
        Review the Arabic names of one category at once (D-137): each row shows the English name, the name from the menu
        file, a box to correct it and an "Approve" tick. Only ticked rows are approved (an approved name is changed on the item's own screen); the source file never changes.
    --}}
    @php $M = 'dashboard.menu.'; @endphp
    <x-ui.page-header :title="__($M.'review_title', ['category' => $category->name_en])" :description="__($M.'review_help')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.menu.index', ['category' => $category->code])">{{ __($M.'back_to_menu') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @php $catBag = $errors->getBag('category'); @endphp
    <section class="ui-record__section" aria-labelledby="category-name-title">
        <h2 id="category-name-title" class="ui-record__title">{{ __($M.'category_name') }}</h2>
        <form class="ui-record__form" method="post" action="{{ route('dashboard.menu.category.name', $category) }}">
            @csrf
            @method('PUT')
            <p class="ui-review__en" lang="en" dir="ltr">{{ $category->name_en }}</p>
            <x-ui.field :label="__($M.'fields.name_ar')" for="category_name_ar" :hint="filled($category->suggested_name_ar) ? __($M.'fields.suggested', ['name' => $category->suggested_name_ar]) : null" :error="$catBag->first('category_name_ar')">
                <x-ui.input id="category_name_ar" name="category_name_ar" lang="ar" dir="rtl" maxlength="120" :value="$catBag->any() ? old('category_name_ar') : ($category->name_ar ?? $category->suggested_name_ar)" />
            </x-ui.field>
            <x-ui.checkbox :label="__($M.'fields.approve_ar')" name="approve_category" value="1" id="approve_category" :checked="$catBag->any() ? (bool) old('approve_category') : $categoryApproved" />
            <x-ui.button type="submit" variant="secondary">{{ __('dashboard.save') }}</x-ui.button>
        </form>
    </section>

    @if ($errors->any())
        <x-ui.error-summary :errors="collect($errors->getMessages())->mapWithKeys(fn ($m, $k) => [str_replace('names.', 'name-', $k) => $m])->all()" :title="__('dashboard.pages.errors.summary')" />
    @endif

    <form class="ui-editor" method="post" action="{{ route('dashboard.menu.review.save', $category) }}">
        @csrf
        @method('PUT')
        <h2 class="ui-record__title">{{ __($M.'items_names') }}</h2>
        <ul class="ui-review" role="list">
            @foreach ($rows as $row)
                @php
                    $p = $row['product'];
                    $value = old('names.'.$p->id, $p->display_name_ar ?? $row['source']);
                @endphp
                <li @class(['ui-review__row', 'ui-review__row--done' => $row['approved']])>
                    <p class="ui-review__en" lang="en" dir="ltr">{{ $p->display_name_en }} <span class="ui-menu-table__code"><bdi>{{ $p->code }}</bdi></span></p>
                    @if ($row['approved'])
                        <p class="ui-review__ar"><span lang="ar" dir="rtl">{{ $p->display_name_ar }}</span>
                            <x-ui.badge variant="success" icon="circle-check">{{ __($M.'review_approved') }}</x-ui.badge></p>
                    @else
                        <x-ui.field :label="__($M.'fields.name_ar')" :for="'name-'.$p->id" :hint="$row['source'] !== null && $row['source'] !== $value ? __($M.'fields.source', ['name' => $row['source']]) : null" :error="$errors->first('names.'.$p->id)">
                            <x-ui.input :id="'name-'.$p->id" :name="'names['.$p->id.']'" lang="ar" dir="rtl" maxlength="120" :value="$value" />
                        </x-ui.field>
                        <x-ui.checkbox :label="__($M.'review_approve')" :name="'approve['.$p->id.']'" value="1" :id="'approve-'.$p->id" :checked="(bool) old('approve.'.$p->id)" />
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="ui-editor__bar">
            <x-ui.button type="submit" size="lg">{{ __($M.'review_save') }}</x-ui.button>
        </div>
    </form>
@endsection
