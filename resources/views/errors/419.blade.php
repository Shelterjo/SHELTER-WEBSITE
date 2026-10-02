{{-- FINAL-QA QA-006: the session ran out. Public forms never land here — the visitor goes back to the form with what
     they typed (bootstrap/app.php); this page covers every other case. --}}
@include('errors.static', ['code' => '419'])
