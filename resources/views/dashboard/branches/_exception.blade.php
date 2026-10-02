{{--
    Add / change one exception. `$exception` (null = new), `$bag` (its error bag), `$prefix` (unique ids), `$action`, `$method`.
    Closures (temporary, emergency) are closed all day; special hours and holidays choose closed or one interval.
--}}
@php
    $H = 'dashboard.hours.';
    $old = $exception === null;
    $v = fn (string $field, $default = null) => $old ? old($field, $default) : $default;
    $kind = $v('kind', $exception?->kind->value ?? 'special');
    $mode = $v('mode', $exception === null ? 'hours' : ($exception->is_closed ? 'closed' : 'hours'));
@endphp
<form class="ui-record__form ui-exception" method="post" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif
    <x-ui.fieldset :legend="__($H.'kind')" :id="$prefix.'kind'" :error="$bag->first('kind')">
        @foreach (\App\Enums\HoursExceptionKind::cases() as $case)
            <x-ui.radio :label="__($H.'kinds.'.$case->value)" name="kind" :value="$case->value" :id="$prefix.'kind-'.$case->value" :checked="$kind === $case->value" :hint="__($H.'kind_help.'.$case->value)" />
        @endforeach
    </x-ui.fieldset>
    <div class="ui-editor__pair">
        <x-ui.field :label="__($H.'starts_on')" :for="$prefix.'starts_on'" :error="$bag->first('starts_on')">
            <x-ui.input type="date" :id="$prefix.'starts_on'" name="starts_on" :value="$v('starts_on', $exception?->starts_on?->format('Y-m-d'))" />
        </x-ui.field>
        <x-ui.field :label="__($H.'ends_on')" :for="$prefix.'ends_on'" :error="$bag->first('ends_on')">
            <x-ui.input type="date" :id="$prefix.'ends_on'" name="ends_on" :value="$v('ends_on', $exception?->ends_on?->format('Y-m-d'))" />
        </x-ui.field>
    </div>
    <div class="ui-exception__hours">
        <x-ui.fieldset :legend="__($H.'mode')" :id="$prefix.'mode'">
            <div class="ui-editor__options">
                @foreach (['hours', 'closed'] as $option)
                    <x-ui.radio :label="__($H.'modes.'.$option)" name="mode" :value="$option" :id="$prefix.'mode-'.$option" :checked="$mode === $option" />
                @endforeach
            </div>
        </x-ui.fieldset>
        <div class="ui-editor__pair">
            <x-ui.field :label="__($H.'opens')" :for="$prefix.'opens_at'" :error="$bag->first('opens_at')">
                <x-ui.input type="time" :id="$prefix.'opens_at'" name="opens_at" step="300" :value="$v('opens_at', $exception?->opens_at ? substr($exception->opens_at, 0, 5) : null)" />
            </x-ui.field>
            <x-ui.field :label="__($H.'closes')" :for="$prefix.'closes_at'">
                <x-ui.input type="time" :id="$prefix.'closes_at'" name="closes_at" step="300" :value="$v('closes_at', $exception?->closes_at ? substr($exception->closes_at, 0, 5) : null)" />
            </x-ui.field>
        </div>
    </div>
    <x-ui.field :label="__($H.'reason')" :for="$prefix.'reason'" :hint="__($H.'reason_hint')" :error="$bag->first('reason')">
        <x-ui.input :id="$prefix.'reason'" name="reason" maxlength="300" :value="$v('reason', $exception?->reason_ar)" />
    </x-ui.field>
    <x-ui.fieldset :legend="__($H.'status')" :id="$prefix.'status'">
        <div class="ui-editor__options">
            @foreach (['draft', 'published'] as $option)
                <x-ui.radio :label="__($H.'statuses.'.$option)" name="status" :value="$option" :id="$prefix.'status-'.$option" :checked="$v('status', $exception?->status->value ?? 'published') === $option" />
            @endforeach
        </div>
    </x-ui.fieldset>
    <x-ui.button type="submit">{{ __($H.'add_save') }}</x-ui.button>
</form>
