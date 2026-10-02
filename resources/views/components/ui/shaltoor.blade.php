{{--
    شلتور — the site assistant (M69). A launcher and one dialog (bottom sheet on phones, a panel at the inline end from
    1024px). Progressive enhancement: the launcher stays hidden until JavaScript is there, and the conversation script
    is fetched on the first open only (resources/js/shaltoor/widget.ts). Every answer comes from the server as plain text
    built from Master Data; nothing here is a business fact. The CSRF token travels in the form, never in a meta tag.
    $page picks the quick suggestions (menu, careers, franchise, locations, default); $branch is the branch page's slug.
--}}
@props([
    'endpoint',
    'welcome',
    'suggestions' => [],
    'page' => 'default',
    'branch' => null,
])
<div class="ui-shaltoor" data-shaltoor data-endpoint="{{ $endpoint }}" data-page="{{ $page }}" @if ($branch) data-branch="{{ $branch }}" @endif
    data-error="{{ __('shaltoor.answers.unavailable') }}" data-typing="{{ __('shaltoor.typing') }}" data-you="{{ __('shaltoor.you') }}">
    <button type="button" class="ui-shaltoor__launcher" hidden data-shaltoor-launcher commandfor="shaltoor" command="show-modal"
        aria-haspopup="dialog" aria-controls="shaltoor" aria-expanded="false" data-ui-dialog-open="shaltoor">
        <x-ui.icon name="messages-square" />
        <span class="ui-shaltoor__launcher-label">{{ __('shaltoor.open') }}</span>
    </button>
    <x-ui.dialog id="shaltoor" variant="sheet-adaptive" :title="__('shaltoor.name')" class="ui-shaltoor__dialog">
        <p class="ui-shaltoor__role">{{ __('shaltoor.role') }}</p>
        <ol class="ui-shaltoor__log" role="log" aria-live="polite" aria-relevant="additions" data-shaltoor-log>
            <li class="ui-shaltoor__message ui-shaltoor__message--bot">{{ $welcome }}</li>
        </ol>
        @if ($suggestions !== [])
            <div class="ui-shaltoor__suggestions" data-shaltoor-suggestions>
                @foreach ($suggestions as $suggestion)
                    <x-ui.chip :toggle="false" data-shaltoor-ask>{{ $suggestion }}</x-ui.chip>
                @endforeach
            </div>
        @endif
        <form class="ui-shaltoor__form" method="post" action="{{ $endpoint }}" data-shaltoor-form>
            @csrf
            <label class="ui-visually-hidden" for="shaltoor-question">{{ __('shaltoor.input_label') }}</label>
            <x-ui.input id="shaltoor-question" name="question" maxlength="{{ (int) config('shaltoor.max_question_length', 500) }}"
                autocomplete="off" enterkeyhint="send" :placeholder="__('shaltoor.input_placeholder')" required />
            <x-ui.button type="submit" icon="arrow-up" icon-only :label="__('shaltoor.send')" />
        </form>
        <p class="ui-shaltoor__privacy">{{ __('shaltoor.privacy') }}</p>
        {{-- Icons the script copies onto answer actions (one icon library, DS-012). --}}
        <template data-shaltoor-icon="call"><x-ui.icon name="phone" size="sm" /></template>
        <template data-shaltoor-icon="whatsapp"><x-ui.icon name="message-circle" size="sm" /></template>
        <template data-shaltoor-icon="directions"><x-ui.icon name="map-pin" size="sm" /></template>
        <template data-shaltoor-icon="link"><x-ui.icon name="arrow-right" size="sm" /></template>
    </x-ui.dialog>
</div>
