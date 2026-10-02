@extends('layouts.dashboard')

@section('title', __('dashboard.shaltoor.title'))

@section('content')
    {{--
        Shaltoor (M69 §23, App\Services\Shaltoor): on/off, its words, the period's numbers and the questions it could
        not answer (grouped, most asked first). The answers' rules and sources are not editable (no free prompt editing).
    --}}
    @php
        $S = 'dashboard.shaltoor.';
        $bag = $errors->getBag('shaltoor');
        $fresh = $bag->any();
        $topic = fn (string $t): string => \Illuminate\Support\Facades\Lang::has($S.'topics.'.$t) ? __($S.'topics.'.$t) : $t;
        $state = $fresh ? (old('enabled') ? 'on' : 'off') : ($enabled ? 'on' : 'off');
    @endphp
    <x-ui.page-header :title="__($S.'title')" :description="__($S.'description')" />
    @if ($fresh)
        <x-ui.error-summary :errors="$bag" :title="__('dashboard.pages.errors.summary')" id="shaltoor-errors" />
    @endif

    <div class="ui-record">
        <form class="ui-record" method="post" action="{{ route('dashboard.shaltoor.update') }}">
            @csrf
            @method('PUT')
            <section class="ui-record__section ui-record__section--status" aria-labelledby="status-title">
                <h2 id="status-title" class="ui-record__title">{{ __($S.'status_title') }}</h2>
                <p class="ui-record__status" data-shaltoor-state>
                    <span>{{ __($S.'now') }}:</span>
                    <x-ui.badge :variant="$enabled ? 'success' : 'neutral'" :icon="$enabled ? 'circle-check' : 'pause'">{{ __($S.($enabled ? 'live' : 'off')) }}</x-ui.badge>
                </p>
                <x-ui.fieldset :legend="__($S.'status_title')" id="enabled">
                    <div class="ui-editor__options">
                        <x-ui.radio :label="__($S.'on_label')" name="enabled" value="1" id="enabled-on" :checked="$state === 'on'" />
                        <x-ui.radio :label="__($S.'off_label')" name="enabled" value="0" id="enabled-off" :checked="$state === 'off'" />
                    </div>
                </x-ui.fieldset>
                <x-ui.alert :variant="$aiConnected ? 'info' : 'warning'" :title="__($S.'ai_title')">{{ __($S.($aiConnected ? 'ai_connected' : 'ai_pending')) }}</x-ui.alert>
            </section>

            <section class="ui-record__section" aria-labelledby="texts-title">
                <h2 id="texts-title" class="ui-record__title">{{ __($S.'texts_title') }}</h2>
                <p class="ui-note">{{ __($S.'texts_help') }}</p>
                <div class="ui-editor__pair">
                    @foreach (['ar', 'en'] as $lang)
                        <x-ui.field :label="__($S.'welcome_'.$lang)" :for="'welcome_'.$lang" :hint="__($S.'default_hint', ['text' => $defaults[$lang]])" :error="$bag->first('welcome_'.$lang)" optional>
                            <x-ui.textarea :id="'welcome_'.$lang" :name="'welcome_'.$lang" rows="3" maxlength="400" :dir="$lang === 'ar' ? 'rtl' : 'ltr'" :lang="$lang"
                                :value="$fresh ? old('welcome_'.$lang) : $welcome[$lang]" />
                        </x-ui.field>
                    @endforeach
                </div>
                <div class="ui-editor__pair">
                    @foreach (['ar', 'en'] as $lang)
                        <x-ui.field :label="__($S.'suggestions_'.$lang)" :for="'suggestions_'.$lang" :hint="__($S.'default_hint', ['text' => implode(' · ', $defaultSuggestions[$lang])])" :error="$bag->first('suggestions_'.$lang)" optional>
                            <x-ui.textarea :id="'suggestions_'.$lang" :name="'suggestions_'.$lang" rows="5" :dir="$lang === 'ar' ? 'rtl' : 'ltr'" :lang="$lang"
                                :value="$fresh ? old('suggestions_'.$lang) : $suggestions[$lang]" />
                        </x-ui.field>
                    @endforeach
                </div>
                <div>
                    <x-ui.button type="submit">{{ __($S.'save') }}</x-ui.button>
                </div>
            </section>
        </form>

        <section class="ui-record__section" aria-labelledby="stats-title">
            <h2 id="stats-title" class="ui-record__title">{{ __($S.'stats_title') }}</h2>
            <nav class="ui-shaltoor-periods" aria-label="{{ __($S.'period') }}">
                @foreach ($periods as $p)
                    <x-ui.chip :href="route('dashboard.shaltoor', ['days' => $p])" :current="$p === $days">{{ __($S.'periods.'.$p) }}</x-ui.chip>
                @endforeach
            </nav>
            @if ($stats['total'] === 0)
                <p class="ui-note">{{ __($S.'no_data') }}</p>
            @else
                <div class="ui-tiles">
                    <x-ui.stat-tile :label="__($S.'asked')" :value="$stats['total']" />
                    <x-ui.stat-tile :label="__($S.'answered')" :value="$stats['answered']" />
                    <x-ui.stat-tile :label="__($S.'not_answered')" :value="$stats['unanswered']" />
                    <x-ui.stat-tile :label="__($S.'with_ai')" :value="$stats['ai']" />
                </div>
                <h3 class="ui-record__subtitle">{{ __($S.'topics_title') }}</h3>
                <ul class="ui-record__lines">
                    @foreach ($stats['topics'] as $name => $n)
                        <li><span>{{ $topic($name) }} — <bdi>{{ $n }}</bdi></span></li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="ui-record__section" aria-labelledby="unanswered-title" id="unanswered">
            <h2 id="unanswered-title" class="ui-record__title">{{ __($S.'unanswered_title') }}</h2>
            <p class="ui-note">{{ __($S.'unanswered_help') }}</p>
            @if ($unanswered === [])
                <x-ui.empty-state :title="__($S.'unanswered_empty')" :level="3" />
            @else
                <ul class="ui-record__notes">
                    @foreach ($unanswered as $q)
                        <li class="ui-record__note" data-shaltoor-unanswered>
                            <p class="ui-record__text"><bdi>{{ $q['question'] }}</bdi></p>
                            <p class="ui-record__status">
                                <x-ui.badge>{{ trans_choice($S.'times', $q['count'], ['count' => $q['count']]) }}</x-ui.badge>
                                @if ($q['last_at'])
                                    <span class="ui-note">{{ __($S.'last_asked', ['date' => \Illuminate\Support\Carbon::parse($q['last_at'])->timezone('Asia/Amman')->format('Y-m-d H:i')]) }}</span>
                                @endif
                            </p>
                            <form method="post" action="{{ route('dashboard.shaltoor.handled') }}">
                                @csrf
                                <input type="hidden" name="normalized" value="{{ $q['normalized'] }}">
                                <input type="hidden" name="days" value="{{ $days }}">
                                <x-ui.button type="submit" variant="secondary" size="sm" icon="check">{{ __($S.'handled') }}</x-ui.button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
