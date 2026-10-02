{{-- FINAL-QA QA-006: too many requests (for example the search limit). --}}
@include('errors.static', ['code' => '429'])
