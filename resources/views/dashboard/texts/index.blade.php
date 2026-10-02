@extends('layouts.dashboard')

@section('title', __('dashboard.texts.title'))

@section('content')
    {{--
        Content → Site texts (M50): page by page, every listed fixed text in Arabic and English side by side, the
        original wording under each box, "Changed" when the Owner's wording shows. Empty = back to the original.
    --}}
    @php $T = 'dashboard.texts.'; @endphp
    <x-ui.page-header :title="__($T.'title')" :description="__($T.'description')" />

    <div class="ui-menu-item">
        @foreach ($groups as $group => $keys)
            @php
                $bag = $errors->getBag('texts-'.$group);
                $overrides = \App\Services\Content\SiteTexts::overrides();
            @endphp
            <x-ui.disclosure :summary="__($T.'groups.'.$group)" :open="$open === $group || $bag->any()" id="texts-{{ $group }}">
                @if ($bag->any())
                    <x-ui.error-summary :errors="$bag" :title="__('dashboard.pages.errors.summary')" :id="'texts-errors-'.$group" />
                @endif
                <form class="ui-record__form" method="post" action="{{ route('dashboard.texts.update', $group) }}">
                    @csrf
                    @method('PUT')
                    @foreach ($keys as $key)
                        @php
                            [$file, $path] = explode('.', $key, 2);
                            $isMeta = str_starts_with($key, 'site.meta.');
                            $isTitle = str_starts_with($key, 'site.titles.') || in_array($key, ['site.home.title', 'menu.page_title', 'site.branch.title'], true);
                        @endphp
                        <x-ui.fieldset :legend="__($T.'labels.'.str_replace('.', '_', $key))" :id="'f-'.str_replace(['.', '_'], '-', $key)">
                            <div class="ui-bilingual">
                                @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
                                    @php
                                        $id = \App\Services\Content\SiteTexts::fieldId($key, $locale);
                                        $original = \App\Services\Content\SiteTexts::original($key, $locale);
                                        $changed = isset($overrides[$locale][$file][$path]);
                                        $value = $bag->any() ? data_get(old('texts', []), [$key, $locale]) : \App\Services\Content\SiteTexts::current($key, $locale);
                                        $hint = ($original === '' ? __($T.'original_empty') : __($T.'original', ['text' => $original])).($isMeta ? ' '.__($T.'meta_hint') : '').($isTitle ? ' '.__($T.'title_hint') : '');
                                    @endphp
                                    <div class="ui-bilingual__column">
                                        <x-ui.field :label="__('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english')).($changed ? ' — '.__($T.'changed') : '')" :for="$id" :hint="$hint" :error="$bag->first($id)" optional>
                                            <x-ui.textarea :id="$id" :name="'texts['.$key.']['.$locale.']'" rows="2" maxlength="300" :lang="$locale" :dir="$dir" :value="$value" />
                                        </x-ui.field>
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.fieldset>
                    @endforeach
                    <x-ui.button type="submit">{{ __($T.'save') }}</x-ui.button>
                </form>
            </x-ui.disclosure>
        @endforeach
    </div>
@endsection
