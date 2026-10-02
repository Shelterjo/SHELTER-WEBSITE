{{-- One old link's fields (new or edit). $prefix = field id prefix, $bag = its error bag, $fresh = the bag has errors. --}}
@php $R = 'dashboard.redirects.'; @endphp
<div class="ui-editor__pair">
    <x-ui.field :label="__($R.'fields.source_path')" :for="$prefix.'-source'" :hint="__($R.'fields.source_hint')" :error="$bag->first('source_path')">
        <x-ui.input :id="$prefix.'-source'" name="source_path" dir="ltr" maxlength="500" :value="$fresh ? old('source_path') : $row?->source_path" />
    </x-ui.field>
    <x-ui.field :label="__($R.'fields.target')" :for="$prefix.'-target'" :hint="__($R.'fields.target_hint')" :error="$bag->first('target')">
        <x-ui.input :id="$prefix.'-target'" name="target" dir="ltr" maxlength="500" :value="$fresh ? old('target') : $row?->target" />
    </x-ui.field>
</div>
<div class="ui-editor__pair">
    <x-ui.field :label="__($R.'fields.status_code')" :for="$prefix.'-code'" :error="$bag->first('status_code')">
        <x-ui.select :id="$prefix.'-code'" name="status_code" :options="$codeOptions" :selected="(string) ($fresh ? old('status_code') : ($row?->status_code ?? 301))" />
    </x-ui.field>
    <x-ui.field :label="__($R.'fields.note')" :for="$prefix.'-note'" :error="$bag->first('note')">
        <x-ui.input :id="$prefix.'-note'" name="note" maxlength="500" :value="$fresh ? old('note') : $row?->note" />
    </x-ui.field>
</div>
