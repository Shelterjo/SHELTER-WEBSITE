{{--
    Internal notes of an application (CAREERS-067, FRAN-058): add one, edit or remove each (both audited, removal is
    soft). `$application`, `$store` (URL), `$update` (fn(ApplicationNote): URL).
--}}
@php
    $date = fn ($d) => $d?->timezone('Asia/Amman')->format('Y-m-d H:i');
@endphp
<section class="ui-record__section" id="notes" aria-labelledby="notes-title">
    <h2 class="ui-record__title" id="notes-title">{{ __('dashboard.requests.sections.notes') }}</h2>
    <form class="ui-record__form" method="post" action="{{ $store }}">
        @csrf
        <x-ui.field :label="__('dashboard.requests.notes_add')" for="note-body" :error="$errors->first('body')">
            <x-ui.textarea id="note-body" name="body" rows="3" maxlength="3000" />
        </x-ui.field>
        <x-ui.button type="submit">{{ __('dashboard.requests.notes_save') }}</x-ui.button>
    </form>
    @if ($application->notes->isEmpty())
        <p class="ui-note">{{ __('dashboard.requests.notes_empty') }}</p>
    @else
        <ul class="ui-record__notes" role="list">
            @foreach ($application->notes as $note)
                <li class="ui-record__note">
                    <p class="ui-record__text">{{ $note->body }}</p>
                    <p class="ui-note">
                        <bdi>{{ __('dashboard.requests.notes_by', ['name' => $note->author?->name ?? '—', 'date' => $date($note->created_at)]) }}</bdi>
                        @if ($note->updated_at?->gt($note->created_at))
                            {{ __('dashboard.requests.notes_edited') }}
                        @endif
                    </p>
                    <x-ui.disclosure :summary="__('dashboard.requests.notes_edit')">
                        <form class="ui-record__form" method="post" action="{{ $update($note) }}">
                            @csrf
                            @method('PUT')
                            <x-ui.field :label="__('dashboard.requests.notes_body')" :for="'note-'.$note->id">
                                <x-ui.textarea :id="'note-'.$note->id" name="body" rows="3" maxlength="3000" :value="$note->body" />
                            </x-ui.field>
                            <x-ui.checkbox :label="__('dashboard.requests.notes_remove')" name="remove" value="1" :id="'note-remove-'.$note->id" />
                            <x-ui.button type="submit" size="sm">{{ __('dashboard.requests.notes_save') }}</x-ui.button>
                        </form>
                    </x-ui.disclosure>
                </li>
            @endforeach
        </ul>
    @endif
</section>
