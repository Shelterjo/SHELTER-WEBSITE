<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * An award or recognition SHELTER received (CONTENT-SOURCE-OF-TRUTH, PUBLISH-GUARD): public only once published, in
 * both languages, and verified — its fact `award.{id}` approved in the Fact Registry with these exact values (PO-032).
 *
 * @property int $id
 * @property string|null $title_ar
 * @property string|null $title_en
 * @property string|null $issuer_ar
 * @property string|null $issuer_en
 * @property int $year
 * @property string|null $description_ar
 * @property string|null $description_en
 * @property string|null $evidence_url
 * @property int|null $media_id
 * @property string $status draft | published | archived
 * @property int $sort
 * @property Carbon|null $archived_at
 */
class Award extends Model
{
    protected $guarded = ['id'];

    /** @return BelongsTo<Media, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function factKey(): string
    {
        return 'award.'.$this->id;
    }

    /**
     * What the Owner verifies — any change after approval takes the award offline until re-approved.
     *
     * @return list<string|int|null>
     */
    public function factValue(): array
    {
        return [$this->title_ar, $this->title_en, $this->issuer_ar, $this->issuer_en, $this->year, $this->evidence_url];
    }
}
