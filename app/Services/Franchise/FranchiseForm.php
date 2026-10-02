<?php

namespace App\Services\Franchise;

use App\Models\Recruitment\ConsentVersion;

/**
 * Is the partnership form open? Only when both approved texts are active in both languages — the non-binding
 * application acknowledgement (PF-02, scope partnership_ack) and the data-processing consent (PF-03, scope
 * partnerships) — and, in production, only once switched on after the release gates. Until then the page (when
 * published) shows the franchise inquiries contact.
 */
final class FranchiseForm
{
    public const SCOPE_CONSENT = 'partnerships';

    public const SCOPE_ACKNOWLEDGEMENT = 'partnership_ack';

    public function isOpen(): bool
    {
        return $this->consent() !== null && $this->acknowledgement() !== null
            && (! app()->isProduction() || config('franchise.form_enabled_in_production') === true);
    }

    public function consent(): ?ConsentVersion
    {
        return $this->active(self::SCOPE_CONSENT);
    }

    public function acknowledgement(): ?ConsentVersion
    {
        return $this->active(self::SCOPE_ACKNOWLEDGEMENT);
    }

    private function active(string $scope): ?ConsentVersion
    {
        return ConsentVersion::query()->where('scope', $scope)->where('is_active', true)
            ->where('active_from', '<=', now())->whereNotNull('text_en')->orderByDesc('active_from')->first();
    }
}
