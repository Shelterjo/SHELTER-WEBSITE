{{--
    One required radio question of an application form (careers, partnerships): legend = the question, options = value =>
    label. `stack` puts one option per line (sentence-length answers).
--}}
<x-ui.fieldset :legend="$legend" :id="$name" :error="$errors->first($name)" required class="ui-apply__choice">
    <div @class(['ui-apply__options', 'ui-apply__options--stack' => $stack ?? false])>
        @foreach ($options as $value => $label)
            <x-ui.radio :label="$label" :name="$name" :value="$value" :id="$name.'-'.$value" :checked="old($name) === (string) $value" />
        @endforeach
    </div>
</x-ui.fieldset>
