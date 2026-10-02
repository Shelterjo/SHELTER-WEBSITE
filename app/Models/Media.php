<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One asset of THE media library (MEDIA-RIGHTS §2): the file facts, its alternative text, and its rights — who took it,
 * who owns it, under which license, until when, whether the people in it consented and whether the Owner approved it.
 * Public use goes only through App\Services\Media\MediaRights::canUse().
 *
 * @property int $id
 * @property string $code
 * @property string $kind
 * @property string $original_path
 * @property string $mime
 * @property int|null $width
 * @property int|null $height
 * @property int $bytes
 * @property string $sha256
 * @property string|null $alt_ar
 * @property string|null $alt_en
 * @property int $focal_x
 * @property int $focal_y
 * @property string|null $photographer
 * @property string $source
 * @property bool $source_explicitly_approved
 * @property string|null $rights_holder
 * @property string $license
 * @property string|null $license_note
 * @property string $approval_status
 * @property Carbon|null $approved_at
 * @property int|null $approved_by
 * @property string|null $approval_ref
 * @property string $people_consent
 * @property list<array{person: string, consented_at?: string, scopes?: list<string>, document_ref?: string, withdrawn_at?: string|null}>|null $people_consents
 * @property bool $ok_website
 * @property bool $ok_ads
 * @property string|null $restrictions
 * @property Carbon|null $rights_expires_at
 * @property array<string, list<array{width: int, path: string}>>|null $variants
 * @property Carbon|null $variants_generated_at
 * @property Carbon|null $archived_at
 */
class Media extends Model
{
    public const SOURCES = ['shelter', 'contracted', 'partner', 'other', 'stock', 'ai', 'google', 'legacy_site'];

    /** MEDIA-005: never usable without an explicit approval of the asset itself. */
    public const RESTRICTED_SOURCES = ['stock', 'ai', 'google', 'legacy_site'];

    public const APPROVED = 'APPROVED';

    protected $table = 'media';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'source_explicitly_approved' => 'boolean',
            'approved_at' => 'datetime',
            'people_consents' => 'array',
            'ok_website' => 'boolean',
            'ok_ads' => 'boolean',
            'rights_expires_at' => 'datetime',
            'variants' => 'array',
            'variants_generated_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /** @return HasMany<MediaUsage, $this> */
    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function alt(string $locale): ?string
    {
        $alt = $locale === 'ar' ? $this->alt_ar : $this->alt_en;

        return filled($alt) ? (string) $alt : null;
    }
}
