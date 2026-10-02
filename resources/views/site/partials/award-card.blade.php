{{-- One verified award (Awards::published row): year, title, issuer; optional image, description and source link. --}}
<article class="ui-award">
    @if ($award['image'] !== null)
        <x-ui.picture :image="$award['image']" ratio="landscape" sizes="(min-width: 1024px) 30vw, (min-width: 600px) 45vw, 100vw" class="ui-award__image" />
    @endif
    <div class="ui-award__body">
        <p class="ui-award__year"><time datetime="{{ $award['year'] }}">{{ $award['year'] }}</time></p>
        <h3 class="ui-award__title">{{ $award['title'] }}</h3>
        <p class="ui-award__issuer"><span class="ui-visually-hidden">{{ __('awards.issuer') }}: </span>{{ $award['issuer'] }}</p>
        @if ($award['description'] !== null && ($full ?? false))
            <p class="ui-award__text">{{ $award['description'] }}</p>
        @endif
        @if ($award['url'] !== null && ($full ?? false))
            <p class="ui-award__link"><a href="{{ $award['url'] }}" rel="noopener" target="_blank">{{ __('awards.source') }}<x-ui.icon name="arrow-right" size="sm" /></a></p>
        @endif
    </div>
</article>
