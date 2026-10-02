@extends('layouts.dashboard')

@section('title', __('dashboard.history.restore_title', ['version' => $version->version, 'item' => $name]))

@section('content')
    {{--
        Restore preview (App\Services\Dashboard\VersionRestore): each field as it is now and as it will be. Nothing is
        saved until "Restore this version"; the restore is a new version through the item's own rules (blocked phrases,
        both languages, approval of business facts). If the item changes after this preview, the restore is refused.
    --}}
    @php
        $H = 'dashboard.history.';
        $bag = $errors->getBag('restore');
        $notes = match ($kind) {
            \App\Models\Page::class => [__($H.'notes.page', ['state' => __($H.'values.'.($state instanceof \BackedEnum ? $state->value : 'draft'))])],
            \App\Models\Branch::class => [__($H.'notes.fact'), __($H.'notes.branch')],
            \App\Models\Setting::class => [__($H.'notes.setting')],
            \App\Models\Product::class => [__($H.'notes.price')],
            default => [],
        };
    @endphp
    <x-ui.page-header :title="__($H.'restore_title', ['version' => $version->version, 'item' => $name])" :description="__($H.'restore_description')">
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('dashboard.history.versions', [$type, $version->versionable_id])">{{ __($H.'back_to_versions') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($bag->any())
        <x-ui.alert variant="danger" :title="__($H.'errors.title')">
            <ul class="ui-record__lines" role="list">
                @foreach ($bag->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    @if ($blocker !== null)
        <x-ui.alert variant="warning">{{ __($H.'blocked.'.$blocker) }}</x-ui.alert>
    @elseif ($lines === [])
        <x-ui.alert variant="info">{{ __($H.'errors.same') }}</x-ui.alert>
    @else
        <section class="ui-record__section ui-history__entry" aria-labelledby="changes-title">
            <h2 class="ui-record__title" id="changes-title">{{ trans_choice($H.'changes_title', count($lines), ['count' => count($lines)]) }}</h2>
            <dl class="ui-facts">
                @foreach ($lines as $line)
                    <div>
                        <dt><bdi>{{ $line['field'] }}</bdi></dt>
                        <dd class="ui-record__text">{{ __($H.'now') }} <bdi>{{ $line['now'] }}</bdi></dd>
                        <dd class="ui-record__text">{{ __($H.'after_restore') }} <bdi>{{ $line['then'] }}</bdi></dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <form class="ui-record__section ui-record__form" method="post" action="{{ route('dashboard.history.restore.run', $version) }}">
            @csrf
            <input type="hidden" name="fingerprint" value="{{ $fingerprint }}">
            @foreach ($notes as $note)
                <p class="ui-note">{{ $note }}</p>
            @endforeach
            <p class="ui-note">{{ __($H.'notes.new_version') }}</p>
            <x-ui.field :label="__($H.'note')" for="note" :hint="__($H.'note_hint')" optional>
                <x-ui.input id="note" name="note" maxlength="200" :value="old('note')" />
            </x-ui.field>
            <div class="ui-cluster">
                <x-ui.button type="submit" icon="rotate-cw">{{ __($H.'restore_button') }}</x-ui.button>
                <x-ui.button variant="ghost" :href="route('dashboard.history.versions', [$type, $version->versionable_id])">{{ __($H.'cancel') }}</x-ui.button>
            </div>
        </form>
    @endif
@endsection
