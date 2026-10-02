@extends('layouts.dashboard')

@section('title', __('dashboard.contacts.title'))

@section('content')
    {{--
        Business data → Contact numbers (ContactsEditor): each number with where it may appear (fixed, D-057), its value,
        whether it is public, and a reason; then the social accounts (shown only once switched on — CONTACT-026/027).
    --}}
    @php $C = 'dashboard.contacts.'; @endphp
    <x-ui.page-header :title="__($C.'title')" :description="__($C.'description')" />

    <div class="ui-record">
        @foreach ($points as $row)
            @php
                $p = $row['point'];
                $bag = $errors->getBag('c'.$p->id);
                $email = $p->kind === \App\Enums\ContactKind::Email;
                $fresh = $bag->any();
            @endphp
            <form class="ui-record__section" id="c{{ $p->id }}" method="post" action="{{ route('dashboard.contacts.update', $p) }}" aria-labelledby="c{{ $p->id }}-title">
                @csrf
                @method('PUT')
                <h2 class="ui-record__title" id="c{{ $p->id }}-title">{{ app()->getLocale() === 'ar' ? $p->label_ar : $p->label_en }}</h2>
                <p class="ui-record__status">
                    @if (! $p->is_public)
                        <x-ui.badge>{{ __($C.'hidden') }}</x-ui.badge>
                    @elseif ($row['approved'])
                        <x-ui.badge variant="success" icon="circle-check">{{ __($C.'approved') }}</x-ui.badge>
                    @else
                        <x-ui.badge variant="warning" icon="circle-alert">{{ __($C.'not_approved') }}</x-ui.badge>
                    @endif
                    @if ($row['display'] !== null)
                        <bdi dir="ltr">{{ $row['display'] }}</bdi>
                    @endif
                </p>
                <p class="ui-note">{{ __($C.'appears.'.$p->kind->value) }}</p>
                <div class="ui-editor__pair">
                    <x-ui.field :label="__($C.($email ? 'value_email' : 'value'))" :for="'c'.$p->id.'-value'" :hint="$email ? null : __($C.'value_hint')" :error="$bag->first('value')">
                        <x-ui.input :type="$email ? 'email' : 'tel'" :id="'c'.$p->id.'-value'" name="value" dir="ltr" :value="$fresh ? old('value') : $p->value" maxlength="120" />
                    </x-ui.field>
                    <x-ui.field :label="__($C.'reason')" :for="'c'.$p->id.'-reason'" :hint="__('dashboard.hours.reason_hint')" :error="$bag->first('reason')">
                        <x-ui.input :id="'c'.$p->id.'-reason'" name="reason" maxlength="300" :value="$fresh ? old('reason') : ''" />
                    </x-ui.field>
                </div>
                <x-ui.checkbox :label="__($C.'public')" name="is_public" value="1" :id="'c'.$p->id.'-public'" :checked="$fresh ? (bool) old('is_public') : $p->is_public" />
                <div class="ui-record__archive">
                    <x-ui.button type="submit">{{ __($C.'save') }}</x-ui.button>
                </div>
            </form>
        @endforeach

        <section class="ui-record__section" aria-labelledby="social-title">
            <h2 class="ui-record__title" id="social-title">{{ __($C.'social_title') }}</h2>
            <p class="ui-note">{{ __($C.'social_help') }}</p>
            <div class="ui-social-grid">
                @foreach ($platforms as $platform)
                    @php
                        $link = $social->get($platform);
                        $bag = $errors->getBag('s-'.$platform);
                        $fresh = $bag->any();
                    @endphp
                    <form class="ui-week__day" id="s-{{ $platform }}" method="post" action="{{ route('dashboard.contacts.social', $platform) }}">
                        @csrf
                        @method('PUT')
                        <p class="ui-week__name" lang="en" dir="ltr">{{ __($C.'platforms.'.$platform) }}</p>
                        <x-ui.field :label="__($C.'url')" :for="'s-'.$platform.'-url'" :error="$bag->first('url')">
                            <x-ui.input type="url" :id="'s-'.$platform.'-url'" name="url" dir="ltr" inputmode="url" maxlength="255" :value="$fresh ? old('url') : $link?->url" />
                        </x-ui.field>
                        <x-ui.checkbox :label="__($C.'active')" name="is_active" value="1" :id="'s-'.$platform.'-active'" :checked="$fresh ? (bool) old('is_active') : (bool) $link?->is_active" />
                        <x-ui.button type="submit" size="sm" variant="outline">{{ __($C.'save') }}</x-ui.button>
                    </form>
                @endforeach
            </div>
        </section>
    </div>
@endsection
