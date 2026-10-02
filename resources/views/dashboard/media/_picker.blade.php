{{--
    Image picker for editors (awards, SHELTER Family): radio choices of the images that may appear on the website now,
    each with its private preview. `$name`, `$selected` (media id or null), `$images` (list<Media>), `$legend`, `$id`,
    `$none` (label of the "no image" choice), `$empty` (shown when no image is usable yet).
--}}
<x-ui.fieldset :legend="$legend" :id="$id" :error="$errors->first($name)">
    @if ($images === [])
        <p class="ui-note">{{ $empty }} <a href="{{ route('dashboard.media.index') }}">{{ __('dashboard.media.title') }}</a></p>
    @endif
    <div class="ui-picker">
        <label class="ui-picker__choice">
            <input class="ui-picker__input" type="radio" name="{{ $name }}" value="" @checked($selected === null)>
            <span class="ui-picker__none">{{ $none }}</span>
        </label>
        @foreach ($images as $image)
            <label class="ui-picker__choice">
                <input class="ui-picker__input" type="radio" name="{{ $name }}" value="{{ $image->id }}" @checked((int) $selected === $image->id)>
                <img class="ui-picker__image" src="{{ route('dashboard.media.preview', $image) }}" alt="{{ $image->alt(app()->getLocale()) ?? $image->code }}" width="160" height="120" loading="lazy" decoding="async">
                <span class="ui-picker__code"><bdi>{{ $image->code }}</bdi></span>
            </label>
        @endforeach
    </div>
</x-ui.fieldset>
