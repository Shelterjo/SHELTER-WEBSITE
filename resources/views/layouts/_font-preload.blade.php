{{-- Arabic pages: the text (Light) and heading (Bold) faces of Noto Kufi Arabic, and Poppins Regular (the digits of hours
     and prices), load with the page, before the first paint, so the text does not reflow when they swap in (CLS — D-329).
     English pages carry almost no Arabic: no preload. --}}
@if (app()->getLocale() === 'ar')
    <link rel="preload" as="font" type="font/woff2" href="{{ \Illuminate\Support\Facades\Vite::asset('resources/fonts/noto-kufi-arabic/noto-kufi-arabic-arabic-300-normal.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ \Illuminate\Support\Facades\Vite::asset('resources/fonts/noto-kufi-arabic/noto-kufi-arabic-arabic-700-normal.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ \Illuminate\Support\Facades\Vite::asset('resources/fonts/poppins/poppins-latin-400-normal.woff2') }}" crossorigin>
@endif
