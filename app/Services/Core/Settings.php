<?php

namespace App\Services\Core;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Global settings store, including the Brand and Global Website entities (`brand.*`, `website.*`).
 * Every change is versioned and audited. Public pages must read business values through MasterData
 * (fact-checked), not directly from here.
 */
final class Settings
{
    private const CACHE_KEY = 'shelter:settings:v1';

    /** @var array<string, mixed>|null */
    private ?array $memo = null;

    public function __construct(
        private readonly Cache $cache,
        private readonly AuditLogger $audit,
        private readonly Versions $versions,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function set(string $key, mixed $value, ?User $by = null, ?string $reason = null, string $classification = 'INTERNAL'): Setting
    {
        $setting = Setting::query()->firstOrNew(['key' => $key]);
        $before = $setting->exists ? $setting->value : null;
        $setting->fill([
            'group' => explode('.', $key, 2)[0],
            'value' => $value,
            'classification' => $classification,
            'updated_by' => $by?->id,
        ])->save();

        $this->versions->record($setting, 'published', ['key' => $key, 'value' => $value], $reason, $by);
        $this->audit->record('settings.updated', $setting, ['before' => [$key => $before], 'after' => [$key => $value]], actor: $by);
        $this->flush();

        return $setting;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        if ($this->memo === null) {
            /** @var array<string, mixed> $values */
            $values = $this->cache->rememberForever(self::CACHE_KEY, fn (): array => Setting::query()->pluck('value', 'key')->all());
            $this->memo = $values;
        }

        return $this->memo;
    }

    public function flush(): void
    {
        $this->memo = null;
        $this->cache->forget(self::CACHE_KEY);
    }
}
