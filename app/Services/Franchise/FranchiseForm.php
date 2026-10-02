<?php

namespace App\Services\Franchise;

use App\Models\Recruitment\ConsentVersion;
use App\Services\MasterData\MasterData;

/**
 * Is the partnership form open? Only when its approved texts exist — the consent (PF-03, both languages) and the
 * non-commitment disclaimer (PF-02, both languages, approved through the Fact Registry) — and, in production, only once
 * switched on after the release gates. Until then the page (when published) shows the franchise inquiries contact.
 */
final class FranchiseForm
{
    public function __construct(private readonly MasterData $data) {}

    public function isOpen(): bool
    {
        return $this->consent() !== null
            && $this->disclaimer('ar') !== null && $this->disclaimer('en') !== null
            && (! app()->isProduction() || config('franchise.form_enabled_in_production') === true);
    }

    public function consent(): ?ConsentVersion
    {
        return ConsentVersion::query()->where('scope', 'partnerships')->where('is_active', true)
            ->whereNotNull('text_en')->orderByDesc('active_from')->first();
    }

    public function disclaimer(string $locale): ?string
    {
        /** @var array<string, string> $keys */
        $keys = config('franchise.disclaimer_keys');
        $value = isset($keys[$locale]) ? $this->data->setting($keys[$locale]) : null;

        return is_string($value) && trim($value) !== '' ? $value : null;
    }
}
