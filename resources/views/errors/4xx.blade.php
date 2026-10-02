{{-- FINAL-QA QA-006: any other client error, in the site's design and language. --}}
@include('errors.static', ['code' => '4xx', 'status' => isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : null, 'retry' => false])
