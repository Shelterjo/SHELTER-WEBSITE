{{--
    One upload area (CAREERS-REQUIREMENTS §3 #18): "drag files here or click to upload". The CV is required, other files
    optional, no announced limit (technical guards appear only when reached). The list shows files already received by
    the server (they survive a failed submit); the CV badge marks the detected CV; when detection is unsure the applicant
    picks it (never a guess). With JavaScript each file uploads on its own with progress (resources/js/careers/form.ts).
--}}
<div class="ui-upload" id="files" data-careers-upload>
    @if ($errors->has('files'))
        <p class="ui-field__error" id="files-error">
            <x-ui.icon name="circle-alert" size="sm" />
            <span><span class="ui-visually-hidden">{{ __('ui.error_prefix') }}</span> {{ $errors->first('files') }}</span>
        </p>
    @endif
    <label class="ui-upload__zone" for="files-input" data-careers-drop>
        <x-ui.icon name="upload" size="lg" />
        <span class="ui-upload__title">{{ __('careers.upload.drop') }}</span>
        <span class="ui-upload__hint" id="files-hint">{{ __('careers.upload.hint') }}</span>
    </label>
    <input class="ui-upload__input" type="file" id="files-input" name="files[]" multiple aria-describedby="files-hint{{ $errors->has('files') ? ' files-error' : '' }}">
    <ul class="ui-upload__list" role="list" aria-live="polite" data-careers-files>
        @foreach ($drafts as $file)
            <li class="ui-upload__item" data-file-id="{{ $file->id }}">
                <x-ui.icon name="file-text" size="sm" />
                <span class="ui-upload__name" dir="auto">{{ $file->original_filename }}</span>
                @if ($cv['primary'] === $file->id)
                    <x-ui.badge>{{ __('careers.upload.cv_badge') }}</x-ui.badge>
                @endif
                <x-ui.button variant="ghost" icon="x" icon-only size="sm" :label="__('careers.upload.remove', ['name' => $file->original_filename])" data-careers-remove hidden />
            </li>
        @endforeach
    </ul>
    <div data-careers-cv-choice @if ($cv['state'] !== 'needs_choice') hidden @endif>
        <x-ui.fieldset :legend="__('careers.upload.which_cv')" id="primary_attachment" :hint="__('careers.upload.which_cv_hint')">
            <div class="ui-apply__options" data-careers-cv-options>
                @foreach ($drafts as $file)
                    <x-ui.radio :label="$file->original_filename" name="primary_attachment" :value="$file->id" :id="'cv-'.$file->id"
                        :checked="(string) old('primary_attachment') === (string) $file->id" />
                @endforeach
            </div>
        </x-ui.fieldset>
    </div>
</div>
