<?php

namespace App\Services\Recruitment;

use App\Models\Recruitment\ConsentVersion;
use App\Models\Recruitment\JordanCity;
use Illuminate\Support\Collection;

/**
 * Is the careers form open? Only when its approved data is ready — a verified city list (PENDING DATA VERIFICATION
 * until the Owner approves one), the active consent text, and the identity keys on the server — and, in production,
 * only once switched on after the production gates (real applicant data stays blocked until then — M36).
 */
final class CareersForm
{
    public function isOpen(): bool
    {
        return $this->cities()->isNotEmpty()
            && $this->consent() !== null
            && app(IdentityVault::class)->isConfigured()
            && (! app()->isProduction() || config('careers.form_enabled_in_production') === true);
    }

    /** @return Collection<int, JordanCity> */
    public function cities(): Collection
    {
        return JordanCity::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name_ar')->get();
    }

    public function consent(): ?ConsentVersion
    {
        return ConsentVersion::query()->where('scope', 'careers')->where('is_active', true)->orderByDesc('active_from')->first();
    }
}
