<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Branch services and payment methods (D-033, D-034). value NULL = MISSING — never published as true/false.
 *
 * @property int $id
 * @property int $branch_id
 * @property string $group
 * @property string $key
 * @property bool|null $value
 */
class BranchAttribute extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['value' => 'boolean'];
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
