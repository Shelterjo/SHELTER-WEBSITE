<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A dynamic experience (DYNAMIC-EXPERIENCE-ENGINE §2): campaign · seasonal_theme · event · announcement · recognition.
 * Public reads go through App\Services\Experiences\* only (status, schedule, manual state, emergency switch, both
 * languages, no unconfirmed AI text).
 *
 * @property int|null $media_id
 * @property int $id
 * @property string $type
 * @property int|null $market_id
 * @property string|null $slug
 * @property string|null $title_ar
 * @property string|null $title_en
 * @property string|null $body_ar
 * @property string|null $body_en
 * @property string|null $cta_label_ar
 * @property string|null $cta_label_en
 * @property string|null $cta_url
 * @property array<int, string>|null $placements
 * @property int $priority
 * @property array<int, int>|null $branch_ids
 * @property array<int, int>|null $media_ids
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property string $timezone
 * @property string $status draft | scheduled | active | paused | cancelled | ended
 * @property string|null $manual_state on | off | null
 * @property bool $emergency_disabled
 * @property bool $countdown_enabled
 * @property bool $motion_enabled
 * @property string|null $terms_ar
 * @property string|null $terms_en
 * @property array<string, mixed>|null $details
 * @property string $origin owner | import | ai
 * @property Carbon|null $archived_at
 */
class Experience extends Model
{
    public const TYPES = ['campaign', 'seasonal_theme', 'event', 'announcement', 'recognition'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'placements' => 'array',
            'branch_ids' => 'array',
            'media_ids' => 'array',
            'details' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'archived_at' => 'datetime',
            'emergency_disabled' => 'boolean',
            'countdown_enabled' => 'boolean',
            'motion_enabled' => 'boolean',
            'priority' => 'integer',
        ];
    }

    /** @return BelongsTo<Market, $this> */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function text(string $field, string $locale): ?string
    {
        $value = $this->getAttribute($field.'_'.$locale);

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /** @return BelongsTo<Media, $this> an approved image (only drawn while MediaRights allows it) */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
