{{--
    Status pill — channel sync vocabulary (M33 / MDH-026): SYNCED · PENDING · FAILED · NOT SUPPORTED ·
    MANUAL ACTION REQUIRED · OUT OF SYNC. Icon + text for every status (never colour alone, WCAG 1.4.1).
    `status` accepts the M33 code ("OUT OF SYNC") or its key ("out_of_sync").
--}}
@props([
    'status',
])
@php
    $map = [
        'synced' => ['icon' => 'circle-check', 'tone' => 'success'],
        'pending' => ['icon' => 'clock', 'tone' => 'info'],
        'failed' => ['icon' => 'circle-x', 'tone' => 'danger'],
        'not_supported' => ['icon' => 'ban', 'tone' => 'neutral'],
        'manual_action_required' => ['icon' => 'hand', 'tone' => 'warning'],
        'out_of_sync' => ['icon' => 'refresh-cw-off', 'tone' => 'warning'],
    ];
    $key = str_replace([' ', '-'], '_', strtolower(trim((string) $status)));
    if (! array_key_exists($key, $map)) {
        throw new InvalidArgumentException("x-ui.status-pill: unknown status [{$status}].");
    }
@endphp
<span {{ $attributes->class(['ui-status-pill', 'ui-status-pill--'.$map[$key]['tone']])->merge(['data-status' => strtoupper(str_replace('_', ' ', $key))]) }}>
    <x-ui.icon :name="$map[$key]['icon']" size="sm" />
    <span>{{ __('ui.status.'.$key) }}</span>
</span>
