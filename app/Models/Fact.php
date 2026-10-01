<?php

namespace App\Models;

use App\Enums\FactSource;
use App\Enums\FactStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Fact registry row (FACT-REGISTRY.md). Status changes go through FactRegistry only, never direct updates.
 *
 * @property int $id
 * @property string $code
 * @property string $key
 * @property string $category
 * @property int|null $market_id
 * @property string|null $label_ar
 * @property string|null $label_en
 * @property mixed $value
 * @property string|null $value_hash
 * @property FactStatus $status
 * @property FactSource $source_type
 * @property string|null $source_ref
 * @property string|null $decision_ref
 * @property int|null $approved_by
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 * @property string|null $evidence
 * @property Carbon|null $last_reviewed_at
 * @property Carbon|null $expires_at
 * @property int|null $supersedes_id
 * @property list<string>|null $blocked_phrases
 * @property string $classification
 * @property string|null $notes
 */
class Fact extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'value' => 'json',
            'status' => FactStatus::class,
            'source_type' => FactSource::class,
            'verified_at' => 'datetime',
            'last_reviewed_at' => 'datetime',
            'expires_at' => 'datetime',
            'blocked_phrases' => 'array',
        ];
    }

    /** @return BelongsTo<Fact, $this> */
    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(Fact::class, 'supersedes_id');
    }

    /**
     * The current (non-superseded) row for each key.
     *
     * @param  Builder<Fact>  $query
     */
    public function scopeCurrent(Builder $query): void
    {
        $query->where('status', '!=', FactStatus::Superseded->value);
    }
}
