{{--
    Quick view (CAREERS-065): the essentials of one application for the side panel — name, job, city, experience,
    salary, status (with its action), phone, email, the files. Loaded by the list's script; the full page stays one
    click away. Files download as attachments (never rendered here — CAREERS-080).
--}}
@php
    $job = $application->job;
    $ar = app()->getLocale() === 'ar';
@endphp
<div class="ui-quick" data-quick-body data-quick-title="{{ $job?->full_name }}">
    <dl class="ui-facts ui-facts--grid">
        <div><dt>{{ __('dashboard.requests.fields.reference') }}</dt><dd><bdi>{{ $application->reference_number }}</bdi></dd></div>
        <div><dt>{{ __('dashboard.requests.fields.job') }}</dt><dd>{{ $job?->job_title_text }}</dd></div>
        <div><dt>{{ __('dashboard.requests.fields.city') }}</dt><dd>{{ $job?->city ? ($ar ? $job->city->name_ar : ($job->city->name_en ?? $job->city->name_ar)) : '' }}</dd></div>
        <div><dt>{{ __('dashboard.requests.fields.experience') }}</dt><dd>{{ $job ? __('dashboard.requests.options.experience_band.'.$job->experience_band) : '' }}</dd></div>
        <div><dt>{{ __('dashboard.requests.fields.salary') }}</dt><dd><bdi>{{ $job ? __('dashboard.requests.salary', ['amount' => rtrim(rtrim((string) $job->expected_salary_jod, '0'), '.')]) : '' }}</bdi></dd></div>
        <div><dt>{{ __('dashboard.requests.fields.phone') }}</dt><dd><a href="tel:{{ $job?->phone_normalized }}"><bdi dir="ltr">{{ $job?->phone_normalized }}</bdi></a></dd></div>
        <div><dt>{{ __('dashboard.requests.fields.email') }}</dt><dd><a href="mailto:{{ $job?->email }}"><bdi dir="ltr">{{ $job?->email }}</bdi></a></dd></div>
    </dl>
    @if ($application->attachments->isNotEmpty())
        <ul class="ui-record__files" role="list">
            @foreach ($application->attachments as $file)
                <li class="ui-record__file">
                    <x-ui.icon name="file-text" size="sm" />
                    <a class="ui-record__file-name" href="{{ route('dashboard.requests.attachment', $file) }}"><bdi>{{ $file->original_filename }}</bdi></a>
                </li>
            @endforeach
        </ul>
    @endif
    {{-- Rendered after the panel opened (and marked the application seen): its own moment for the stale-edit check. --}}
    <form class="ui-record__form" method="post" action="{{ route('dashboard.careers.status', $application) }}" data-ui-rendered-at="{{ now()->getTimestamp() }}">
        @csrf
        <x-ui.field :label="__('dashboard.requests.fields.status')" :for="'quick-status-'.$application->id">
            <x-ui.select :id="'quick-status-'.$application->id" name="status" :options="collect($statuses)->mapWithKeys(fn ($s) => [$s => __('dashboard.requests.statuses.'.$s)])->all()" :selected="$application->status" />
        </x-ui.field>
        <div class="ui-record__archive">
            <x-ui.button type="submit" variant="secondary">{{ __('dashboard.requests.quick.save_status') }}</x-ui.button>
            <x-ui.button variant="ghost" :href="route('dashboard.careers.show', $application)" icon="arrow-right">{{ __('dashboard.requests.quick.open') }}</x-ui.button>
        </div>
    </form>
</div>
