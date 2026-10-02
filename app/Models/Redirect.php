<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One old or changed address and where it goes now (App\Services\Seo\LegacyRedirects).
 *
 * @property int $id
 * @property string $source_path
 * @property string|null $target
 * @property int $status_code
 * @property string $state draft · active · archived
 * @property string $origin plan (seeded from the approved migration map) · owner
 * @property string|null $decision_ref
 * @property string|null $note
 * @property int $hits
 * @property Carbon|null $last_hit_at
 * @property int|null $updated_by
 * @property Carbon|null $updated_at
 */
class Redirect extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status_code' => 'integer', 'hits' => 'integer', 'last_hit_at' => 'datetime'];
    }
}
