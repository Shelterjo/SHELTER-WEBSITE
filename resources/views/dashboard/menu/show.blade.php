@extends('layouts.dashboard')

@section('title', (string) $product->display_name_en)

@section('content')
    {{--
        One menu item: its names (the Arabic one reaches the site only once approved), the base price (a new price
        starts on a date; the history stays), and each branch — its own price and availability, or the base.
    --}}
    @php
        $M = 'dashboard.menu.';
        $date = fn ($d) => $d?->format('Y-m-d');
        $namesBag = $errors->getBag('names');
        $priceBag = $errors->getBag('price');
    @endphp
    <x-ui.page-header :title="(string) $product->display_name_en">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.menu.index', ['category' => $product->category->code])">{{ __($M.'back_to_menu') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>
    <p class="ui-record__status">
        <span><bdi>{{ $product->code }}</bdi></span>
        <span>{{ __($M.'current_price') }}:</span>
        @if ($current !== null)
            <strong><x-ui.price :fils="$current->price_fils" /></strong>
        @else
            <x-ui.badge variant="warning" icon="triangle-alert">{{ __($M.'no_price') }}</x-ui.badge>
        @endif
    </p>

    <div class="ui-menu-item">
        <section class="ui-record__section" id="names" aria-labelledby="names-title">
            <h2 id="names-title" class="ui-record__title">{{ __($M.'groups.names') }}</h2>
            @if ($namesBag->any())
                <x-ui.error-summary :errors="$namesBag" :title="__('dashboard.pages.errors.summary')" id="names-errors" />
            @endif
            <form class="ui-record__form" method="post" action="{{ route('dashboard.menu.names', $product) }}">
                @csrf
                @method('PUT')
                <x-ui.field :label="__($M.'fields.name_en')" for="name_en" :error="$namesBag->first('name_en')">
                    <x-ui.input id="name_en" name="name_en" lang="en" dir="ltr" maxlength="120" :value="$namesBag->any() ? old('name_en') : $product->display_name_en" />
                </x-ui.field>
                <x-ui.field :label="__($M.'fields.name_ar')" for="name_ar" :hint="$source !== null ? __($M.'fields.source', ['name' => $source]) : null" :error="$namesBag->first('name_ar')">
                    <x-ui.input id="name_ar" name="name_ar" lang="ar" dir="rtl" maxlength="120" :value="$namesBag->any() ? old('name_ar') : ($product->display_name_ar ?? $source)" />
                </x-ui.field>
                <x-ui.checkbox :label="__($M.'fields.approve_ar')" name="approve_ar" value="1" id="approve_ar" :hint="__($M.'fields.approve_hint')" :checked="$namesBag->any() ? (bool) old('approve_ar') : $approved" />
                <x-ui.button type="submit">{{ __('dashboard.save') }}</x-ui.button>
            </form>
        </section>

        <section class="ui-record__section" id="base-price" aria-labelledby="price-title">
            <h2 id="price-title" class="ui-record__title">{{ __($M.'groups.price') }}</h2>
            @if ($priceBag->any())
                <x-ui.error-summary :errors="collect($priceBag->getMessages())->mapWithKeys(fn ($m, $k) => [$k === 'reason' ? 'price-reason' : $k => $m])->all()" :title="__('dashboard.pages.errors.summary')" id="price-errors" />
            @endif
            <form class="ui-record__form" method="post" action="{{ route('dashboard.menu.price', $product) }}">
                @csrf
                <div class="ui-editor__pair">
                    <x-ui.field :label="__($M.'fields.price')" for="price" :hint="__($M.'fields.price_hint')" :error="$priceBag->first('price')">
                        <x-ui.input id="price" name="price" inputmode="decimal" dir="ltr" maxlength="7" autocomplete="off" :value="$priceBag->any() ? old('price') : null" />
                    </x-ui.field>
                    <x-ui.field :label="__($M.'fields.starts_on')" for="starts_on" :hint="__($M.'fields.starts_hint')" :error="$priceBag->first('starts_on')">
                        <x-ui.input type="date" id="starts_on" name="starts_on" :value="$priceBag->any() ? old('starts_on') : now('Asia/Amman')->toDateString()" />
                    </x-ui.field>
                </div>
                <x-ui.field :label="__($M.'fields.reason')" for="price-reason" :hint="__($M.'fields.reason_hint')" :error="$priceBag->first('reason')">
                    <x-ui.input id="price-reason" name="reason" maxlength="300" :value="$priceBag->any() ? old('reason') : null" />
                </x-ui.field>
                <x-ui.button type="submit">{{ __($M.'save_price') }}</x-ui.button>
            </form>
            <h3 class="ui-record__subtitle">{{ __($M.'groups.history') }}</h3>
            <ul class="ui-record__lines ui-menu-item__lines" role="list">
                @foreach ($history as $row)
                    <li>
                        <x-ui.price :fils="$row->price_fils" />
                        · <bdi>{{ __($M.'from', ['date' => $date($row->valid_from)]) }}</bdi>
                        @if ($row->valid_to !== null)
                            · <bdi>{{ $row->valid_to->lessThan($row->valid_from) ? __($M.'replaced') : __($M.'to', ['date' => $date($row->valid_to)]) }}</bdi>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="ui-record__section" id="branches" aria-labelledby="branches-title">
            <h2 id="branches-title" class="ui-record__title">{{ __($M.'groups.branches') }}</h2>
            <div class="ui-menu-item__branches">
                @foreach ($branches as $b)
                    @php
                        $branch = $b['branch'];
                        $bag = $errors->getBag('b'.$branch->id);
                        $prefix = 'b'.$branch->id.'-';
                        $state = $bag->any() ? (string) old('availability') : $b['state'];
                    @endphp
                    <form class="ui-record__form ui-menu-item__branch" method="post" action="{{ route('dashboard.menu.branch', [$product, $branch]) }}" id="b{{ $branch->id }}">
                        @csrf
                        @method('PUT')
                        <h3 class="ui-record__subtitle">{{ $branchNames[$branch->id] ?? $branch->code }}</h3>
                        <p class="ui-note">
                            {{ __($M.'customer_price') }}:
                            @if ($b['resolved'] !== null)
                                <x-ui.price :fils="$b['resolved']" />
                            @else
                                —
                            @endif
                            · {{ __($M.'shown.'.$b['shown']) }}
                        </p>
                        <x-ui.field :label="__($M.'fields.branch_price')" :for="$prefix.'price'" :hint="__($M.'fields.branch_price_hint', ['price' => $current !== null ? \App\Services\Dashboard\MenuManager::dinars($current->price_fils) : '—'])" :error="$bag->first('price')">
                            <x-ui.input :id="$prefix.'price'" name="price" inputmode="decimal" dir="ltr" maxlength="7" autocomplete="off"
                                :value="$bag->any() ? old('price') : ($b['price'] !== null ? \App\Services\Dashboard\MenuManager::dinars($b['price']) : null)" />
                        </x-ui.field>
                        <x-ui.fieldset :legend="__($M.'fields.availability')" :id="$prefix.'availability'" :error="$bag->first('availability')">
                            @foreach (\App\Services\Dashboard\MenuManager::BRANCH_STATES as $option)
                                <x-ui.radio :label="__($M.'states.'.$option)" name="availability" :value="$option" :id="$prefix.'state-'.$option" :checked="$state === $option" />
                            @endforeach
                        </x-ui.fieldset>
                        <x-ui.field :label="__($M.'fields.reason')" :for="$prefix.'reason'" :hint="__($M.'fields.reason_hint')" :error="$bag->first('reason')">
                            <x-ui.input :id="$prefix.'reason'" name="reason" maxlength="300" :value="$bag->any() ? old('reason') : null" />
                        </x-ui.field>
                        <x-ui.button type="submit" variant="secondary">{{ __($M.'save_branch') }}</x-ui.button>
                    </form>
                @endforeach
            </div>
        </section>

        @if ($versions->isNotEmpty())
            <section class="ui-record__section" aria-labelledby="versions-title">
                <h2 id="versions-title" class="ui-record__title">{{ __($M.'groups.versions') }}</h2>
                <ul class="ui-record__lines ui-menu-item__lines" role="list">
                    @foreach ($versions as $version)
                        @php $snap = $version->snapshot; @endphp
                        <li>
                            <bdi>{{ $version->created_at?->setTimezone('Asia/Amman')->format('Y-m-d H:i') }}</bdi> ·
                            @if (isset($snap['price_fils']))
                                {{ __($M.'version.price', ['price' => \App\Services\Dashboard\MenuManager::dinars((int) $snap['price_fils']), 'date' => $snap['valid_from'] ?? '']) }}
                            @elseif (array_key_exists('override', $snap))
                                @php $branchName = $branchByCode[$snap['branch'] ?? ''] ?? ($snap['branch'] ?? ''); @endphp
                                @if ($snap['override'] === null)
                                    {{ __($M.'version.branch_reset', ['branch' => $branchName]) }}
                                @else
                                    {{ __($M.'version.branch', ['branch' => $branchName, 'what' => implode(' · ', array_filter([
                                        isset($snap['override']['price_fils']) ? \App\Services\Dashboard\MenuManager::dinars((int) $snap['override']['price_fils']) : null,
                                        isset($snap['override']['availability']) ? __($M.'states.'.$snap['override']['availability']) : null,
                                    ]))]) }}
                                @endif
                            @else
                                {{ __($M.'version.names') }}
                            @endif
                            @if (filled($version->reason))
                                · {{ $version->reason }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
@endsection
