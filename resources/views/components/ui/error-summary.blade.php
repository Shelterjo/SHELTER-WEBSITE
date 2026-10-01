{{--
    Error summary (DS-015, WCAG 3.3.1): shown at the top of a form after a failed submit. It takes focus on load and is
    announced; each message links to its field (#field-id). `errors` = a MessageBag or [field => message|messages].
--}}
@props([
    'errors' => [],
    'title' => null,
])
@php
    $messages = match (true) {
        $errors instanceof \Illuminate\Support\ViewErrorBag => $errors->getBag('default')->getMessages(),
        $errors instanceof \Illuminate\Contracts\Support\MessageBag => $errors->getMessages(),
        default => (array) $errors,
    };
    $summaryId = $attributes->get('id', 'error-summary');
@endphp
@if (count($messages) > 0)
    <div {{ $attributes->class('ui-error-summary')->merge([
        'id' => $summaryId,
        'role' => 'alert',
        'tabindex' => '-1',
        'autofocus' => true,
        'aria-labelledby' => $summaryId.'-title',
    ]) }}>
        <h2 class="ui-error-summary__title" id="{{ $summaryId }}-title">
            <x-ui.icon name="circle-alert" />
            {{ $title ?? __('ui.error_summary_title') }}
        </h2>
        <ul class="ui-error-summary__list">
            @foreach ($messages as $field => $fieldMessages)
                @foreach ((array) $fieldMessages as $message)
                    <li><a href="#{{ str_replace('.', '-', (string) $field) }}">{{ $message }}</a></li>
                @endforeach
            @endforeach
        </ul>
    </div>
@endif
