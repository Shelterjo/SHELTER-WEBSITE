@extends('layouts.site')

@section('title', __('site.brand').' · '.__('site.brand_ar'))

@section('content')
    {{-- Root gateway (D-052, D-066): the brand in both languages and the two language doors, nothing else. Its own
         wording (D-068, three options per text) is the Owner's: dashboard → Site texts → site.gateway.lead, shown only
         when written. Built from design-system parts only (FINAL-QA QA-020). --}}
    <div class="ui-page ui-gateway">
        <div class="ui-container">
            <header class="ui-page-intro ui-gateway__intro">
                <h1 class="ui-page-intro__title" lang="en" dir="ltr">{{ __('site.brand') }}</h1>
                <p class="ui-page-intro__lead" lang="ar" dir="rtl">{{ __('site.brand_ar') }}</p>
                @if (filled(__('site.gateway.lead')))
                    <p class="ui-page-intro__lead">{{ __('site.gateway.lead') }}</p>
                @endif
            </header>
            <nav aria-label="{{ __('site.choose_language') }} · Choose language">
                <ul class="ui-cluster ui-gateway__doors" role="list">
                    <li><x-ui.button size="lg" :href="\App\Support\PageUrl::route('home', ['locale' => 'ar'])" lang="ar" hreflang="ar">العربية</x-ui.button></li>
                    <li><x-ui.button size="lg" variant="outline" :href="\App\Support\PageUrl::route('home', ['locale' => 'en'])" lang="en" hreflang="en" dir="ltr">English</x-ui.button></li>
                </ul>
            </nav>
        </div>
    </div>
@endsection
