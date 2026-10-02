{{--
    One person in an image and their consent (MEDIA-RIGHTS §4). `$i` = row index, `$row` = the stored or sent values,
    `$blank` = the empty row used to add a person.
--}}
@php
    $scopes = is_array($row['scopes'] ?? null) ? $row['scopes'] : [];
@endphp
<fieldset @class(['ui-section-card', 'ui-section-card--new' => $blank]) id="p{{ $i }}">
    <legend class="ui-section-card__legend">{{ $blank ? __('dashboard.media.new_person') : __('dashboard.media.fields.person').' '.($i + 1) }}</legend>
    <div class="ui-editor__pair">
        <x-ui.field :label="__('dashboard.media.fields.person')" :for="'p'.$i.'-person'" :error="$errors->first('people.'.$i.'.person')">
            <x-ui.input :id="'p'.$i.'-person'" :name="'people['.$i.'][person]'" :value="$row['person'] ?? null" maxlength="120" />
        </x-ui.field>
        <x-ui.field :label="__('dashboard.media.fields.consented_at')" :for="'p'.$i.'-consented_at'" :error="$errors->first('people.'.$i.'.consented_at')">
            <x-ui.input type="date" :id="'p'.$i.'-consented_at'" :name="'people['.$i.'][consented_at]'" :value="$row['consented_at'] ?? null" />
        </x-ui.field>
    </div>
    <x-ui.fieldset :legend="__('dashboard.media.fields.scopes')" :id="'p'.$i.'-scopes'" :error="$errors->first('people.'.$i.'.scopes')">
        <div class="ui-editor__options">
            @foreach (\App\Models\Media::SCOPES as $scope)
                <x-ui.checkbox :label="__('dashboard.media.scopes.'.$scope)" :name="'people['.$i.'][scopes][]'" :value="$scope" :id="'p'.$i.'-scope-'.$scope" :checked="in_array($scope, $scopes, true)" />
            @endforeach
        </div>
    </x-ui.fieldset>
    <div class="ui-editor__pair">
        <x-ui.field :label="__('dashboard.media.fields.document_ref')" :for="'p'.$i.'-document_ref'" :hint="__('dashboard.media.fields.document_ref_hint')" :error="$errors->first('people.'.$i.'.document_ref')" optional>
            <x-ui.input :id="'p'.$i.'-document_ref'" :name="'people['.$i.'][document_ref]'" :value="$row['document_ref'] ?? null" maxlength="120" />
        </x-ui.field>
        <x-ui.field :label="__('dashboard.media.fields.withdrawn_at')" :for="'p'.$i.'-withdrawn_at'" :error="$errors->first('people.'.$i.'.withdrawn_at')" optional>
            <x-ui.input type="date" :id="'p'.$i.'-withdrawn_at'" :name="'people['.$i.'][withdrawn_at]'" :value="$row['withdrawn_at'] ?? null" />
        </x-ui.field>
    </div>
</fieldset>
