{{--
    One section card of the page editor: type, visibility, order, delete — then the heading and text side by side in
    Arabic and English (stacked on phones). `$i` = form index ("__INDEX__" in the add-section template), `$s` = the
    section (null for a new one), `$n` = its number on screen (the editor script keeps it current when sections move).
--}}
@php
    $v = fn (string $field) => old("sections.$i.$field", $s?->{$field});
    // After a refused save the card shows what was sent: an unticked box is absent from the sent form, not "default".
    $sent = is_array(old("sections.$i"));
    $types = collect(\App\Models\PageSection::TYPES)->mapWithKeys(fn ($t) => [$t => __('dashboard.pages.types.'.$t)])->all();
    $error = $errors->first("sections.$i");
@endphp
<fieldset @class(['ui-section-card', 'ui-section-card--new' => $s === null, 'ui-section-card--error' => filled($error)]) id="section-{{ $i }}" data-section>
    <legend class="ui-section-card__legend">
        {{ __('dashboard.pages.section') }} <span data-section-number>{{ $n }}</span>
        @if ($s === null)
            <span class="ui-section-card__tag">{{ __('dashboard.pages.new_section') }}</span>
        @endif
    </legend>
    @if ($s !== null)
        <input type="hidden" name="sections[{{ $i }}][id]" value="{{ $s->id }}">
    @endif
    @if (filled($error))
        <p class="ui-field__error"><x-ui.icon name="circle-alert" size="sm" /><span>{{ $error }}</span></p>
    @endif
    @if ($s === null)
        <p class="ui-note">{{ __('dashboard.pages.new_section_help') }}</p>
    @endif
    <div class="ui-section-card__controls">
        <x-ui.field :label="__('dashboard.pages.fields.type')" :for="'s'.$i.'-type'">
            <x-ui.select :id="'s'.$i.'-type'" :name="'sections['.$i.'][type]'" :options="$types" :selected="$v('type') ?? 'text'" />
        </x-ui.field>
        <x-ui.field :label="__('dashboard.pages.fields.sort')" :for="'s'.$i.'-sort'" class="ui-section-card__sort">
            <x-ui.input :id="'s'.$i.'-sort'" :name="'sections['.$i.'][sort]'" type="number" inputmode="numeric" min="1" :value="$v('sort') ?? $n" data-section-sort />
        </x-ui.field>
        <x-ui.checkbox :label="__('dashboard.pages.fields.visible')" :name="'sections['.$i.'][visible]'" value="1" :id="'s'.$i.'-visible'"
            :checked="$sent ? (bool) old('sections.'.$i.'.visible') : ($s?->is_visible ?? true)" />
        @if ($s !== null)
            <x-ui.checkbox :label="__('dashboard.pages.fields.remove')" :name="'sections['.$i.'][remove]'" value="1" :id="'s'.$i.'-remove'" :checked="(bool) old('sections.'.$i.'.remove')" />
        @endif
        <div class="ui-section-card__move" hidden data-section-move>
            <x-ui.button type="button" size="sm" variant="ghost" icon="chevron-down" class="ui-section-card__up" data-move="up">{{ __('dashboard.pages.move_up') }}</x-ui.button>
            <x-ui.button type="button" size="sm" variant="ghost" icon="chevron-down" data-move="down">{{ __('dashboard.pages.move_down') }}</x-ui.button>
        </div>
    </div>
    <div class="ui-bilingual">
        @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
            <div class="ui-bilingual__column">
                <p class="ui-bilingual__language">{{ __('dashboard.pages.'.($locale === 'ar' ? 'arabic' : 'english')) }}</p>
                <x-ui.field :label="__('dashboard.pages.fields.heading')" :for="'s'.$i.'-heading-'.$locale" :error="$errors->first('sections.'.$i.'.heading_'.$locale)">
                    <x-ui.input :id="'s'.$i.'-heading-'.$locale" :name="'sections['.$i.'][heading_'.$locale.']'" :value="$v('heading_'.$locale)" maxlength="255" :lang="$locale" :dir="$dir" />
                </x-ui.field>
                <x-ui.field :label="__('dashboard.pages.fields.body')" :for="'s'.$i.'-body-'.$locale" :error="$errors->first('sections.'.$i.'.body_'.$locale)">
                    <x-ui.textarea :id="'s'.$i.'-body-'.$locale" :name="'sections['.$i.'][body_'.$locale.']'" :value="$v('body_'.$locale)" rows="6" :lang="$locale" :dir="$dir" />
                </x-ui.field>
            </div>
        @endforeach
    </div>
</fieldset>
