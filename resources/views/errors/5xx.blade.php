{{-- FINAL-QA QA-006: any other server error. --}}
@include('errors.static', ['code' => '500', 'status' => isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : null])
