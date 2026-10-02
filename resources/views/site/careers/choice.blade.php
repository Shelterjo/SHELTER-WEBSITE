{{-- One required radio question of the careers form: legend = the question, options = value => label (approved). --}}
<x-ui.fieldset :legend="$legend" :id="$name" :error="$errors->first($name)" required class="ui-careers__choice">
    <div class="ui-careers__options">
        @foreach ($options as $value => $label)
            <x-ui.radio :label="$label" :name="$name" :value="$value" :id="$name.'-'.$value" :checked="old($name) === (string) $value" />
        @endforeach
    </div>
</x-ui.fieldset>
