{{--
    Employee of the Month (DX-007/009) — `$recognition` (App\Services\Experiences\ShownRecognition): drawn only when the
    engine returned one, i.e. the person is on SHELTER Family with their recorded consent and the photo may be used on
    the website. Callers render nothing at all otherwise (DX-012). Name and job title exactly as on their profile.
--}}
<article class="ui-recognition" data-experience="{{ $recognition->id }}">
    <x-ui.picture :image="$recognition->image" ratio="portrait" sizes="(min-width: 768px) 30vw, 100vw" class="ui-recognition__photo" />
    <div class="ui-recognition__body">
        <p class="ui-recognition__eyebrow">{{ __('family.month.eyebrow', ['period' => $recognition->period]) }}</p>
        <h2 class="ui-recognition__title" id="recognition-{{ $recognition->id }}">{{ $recognition->title }}</h2>
        <p class="ui-recognition__person"><span class="ui-recognition__name">{{ $recognition->name }}</span> · {{ $recognition->jobTitle }}</p>
        @if ($recognition->text !== null)
            <p class="ui-recognition__text">{{ $recognition->text }}</p>
        @endif
    </div>
</article>
