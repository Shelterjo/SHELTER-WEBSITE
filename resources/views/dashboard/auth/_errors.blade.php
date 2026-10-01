{{-- Error summary at the top of every sign-in form: focused and announced, each message links to its field. --}}
<x-ui.error-summary :errors="$errors->getBag('default')" id="form-errors" />
