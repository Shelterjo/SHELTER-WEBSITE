{{-- Radio (DS-015): x-ui.checkbox with type="radio". Group radios in x-ui.fieldset (legend = the question). --}}
@props([
    'label',
    'hint' => null,
    'error' => null,
])
<x-ui.checkbox type="radio" :label="$label" :hint="$hint" :error="$error" {{ $attributes }} />
