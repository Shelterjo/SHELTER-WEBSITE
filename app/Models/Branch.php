<?php

namespace App\Models;

use App\Enums\BranchStatus;
use App\Enums\BranchType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * A public branch (D-008: DRIVE + HOUSE). Business fields are nullable until approved (facts).
 * Public pages read values through MasterData::branchValue(), never directly (FACT-REGISTRY §6).
 *
 * @property int $id
 * @property string $code
 * @property int $city_id
 * @property string $slug
 * @property BranchType $type
 * @property BranchStatus $status
 * @property bool $is_public
 * @property string|null $name_ar
 * @property string|null $name_en
 * @property string|null $address_ar
 * @property string|null $address_en
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $maps_url
 * @property int $sort
 * @property Carbon|null $archived_at
 * @property-read City $city
 */
class Branch extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => BranchType::class,
            'status' => BranchStatus::class,
            'is_public' => 'boolean',
            'sort' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<City, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** @return HasMany<BranchHour, $this> */
    public function hours(): HasMany
    {
        return $this->hasMany(BranchHour::class);
    }

    /** @return HasMany<HoursException, $this> */
    public function hoursExceptions(): HasMany
    {
        return $this->hasMany(HoursException::class);
    }

    /** @return HasMany<BranchAttribute, $this> */
    public function branchAttributes(): HasMany
    {
        return $this->hasMany(BranchAttribute::class);
    }

    /** @return HasMany<ContactPoint, $this> */
    public function contactPoints(): HasMany
    {
        return $this->hasMany(ContactPoint::class);
    }

    /** @return MorphMany<ExternalReference, $this> */
    public function externalReferences(): MorphMany
    {
        return $this->morphMany(ExternalReference::class, 'entity');
    }

    /** @param Builder<Branch> $query */
    public function scopePublic(Builder $query): void
    {
        $query->where('is_public', true)->whereNull('archived_at')->orderBy('sort');
    }

    public function factKey(string $field): string
    {
        return "branch.{$this->code}.{$field}";
    }
}
