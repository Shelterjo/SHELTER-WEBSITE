<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Stable IDs in external systems (GBP location, future POS/ERP/App). One row per entity + system.
 *
 * @property int $id
 * @property string $entity_type
 * @property int $entity_id
 * @property string $system
 * @property string $external_id
 */
class ExternalReference extends Model
{
    protected $guarded = ['id'];

    /** @return MorphTo<Model, $this> */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
