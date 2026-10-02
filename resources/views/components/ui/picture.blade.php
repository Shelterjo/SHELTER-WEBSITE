{{--
    Picture (MEDIA-006/008/012): an APPROVED library image (App\Services\Media\MediaImage — never a raw path) as
    <picture> with AVIF/WebP sources, its intrinsic width/height (space reserved, CLS 0), the alternative text in the page
    language, lazy loading by default. `ratio` crops to a frame (square · portrait · landscape) around the asset's focus
    point (snapped to the nearest of 3 × 5 positions — classes, no inline style). No image → render nothing.
--}}
@props([
    'image' => null,
    'sizes' => '100vw',
    'ratio' => null,
    'eager' => false,
])
@if ($image instanceof \App\Services\Media\MediaImage)
    @php
        if ($ratio !== null && ! in_array($ratio, ['square', 'portrait', 'landscape'], true)) {
            throw new InvalidArgumentException("x-ui.picture: unknown ratio [{$ratio}] (square · portrait · landscape).");
        }
        // ui-picture--x{0|50|100}-y{0|25|50|75|100} (picture.css)
        $focus = sprintf('ui-picture--x%d-y%d', (int) (round($image->focalX / 50) * 50), (int) (round($image->focalY / 25) * 25));
        $frame = $ratio !== null ? 'ui-picture--'.$ratio : null;
    @endphp
    <picture {{ $attributes->class(['ui-picture', $frame => $frame !== null, $focus => $frame !== null]) }}>
        @foreach ($image->sources as $type => $srcset)
            <source type="{{ $type }}" srcset="{{ $srcset }}" sizes="{{ $sizes }}">
        @endforeach
        <img class="ui-picture__img" src="{{ $image->src }}" alt="{{ $image->alt }}" width="{{ $image->width }}" height="{{ $image->height }}"
            loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async">
    </picture>
@endif
