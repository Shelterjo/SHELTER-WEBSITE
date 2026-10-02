{{--
    The last database backup (DEPLOY-005, OPS-041 — App\Services\Core\DatabaseBackup::status): when, how big, and whether
    the last run worked. Times in Amman; dates and sizes stay left-to-right inside Arabic text.
--}}
@php
    $B = 'dashboard.backup.';
    $state = $backup['state'];
    [$variant, $icon] = match ($state) {
        'ok' => ['success', 'circle-check'],
        'overdue' => ['warning', 'clock'],
        'failed' => ['danger', 'circle-x'],
        default => ['neutral', 'info'],
    };
@endphp
<p class="ui-record__status" data-backup-state="{{ $state }}">
    <span>{{ __($B.'label') }}:</span>
    @if ($backup['latest'])
        <span><bdi dir="ltr">{{ $backup['latest']['at']->timezone('Asia/Amman')->format('Y-m-d H:i') }}</bdi> · <bdi dir="ltr">{{ \Illuminate\Support\Number::fileSize($backup['latest']['bytes'], precision: 1) }}</bdi></span>
    @endif
    <x-ui.badge :variant="$variant" :icon="$icon">
        {{ __($B.'states.'.$state, ['hours' => \App\Services\Core\DatabaseBackup::OVERDUE_HOURS]) }}
        @if ($backup['failed_at'])
            · <bdi dir="ltr">{{ $backup['failed_at']->timezone('Asia/Amman')->format('Y-m-d H:i') }}</bdi>
        @endif
    </x-ui.badge>
</p>
