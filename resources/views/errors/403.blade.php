{{-- FINAL-QA QA-006: styled like every other error page (the framework page uses inline styles the CSP blocks). --}}
@include('errors.static', ['code' => '403', 'retry' => false])
