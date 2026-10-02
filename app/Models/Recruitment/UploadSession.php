<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Draft uploads of one form before it is submitted (RECRUITMENT-DATA-MODEL §2.4). Unsubmitted drafts — never
 * applications — are cleaned after 24 hours. ip_hash is an HMAC used for rate limiting only and goes with the session.
 *
 * @property string $id
 * @property string|null $ip_hash
 * @property Carbon $expires_at
 */
class UploadSession extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    /** @return HasMany<ApplicationAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(ApplicationAttachment::class)->orderBy('id');
    }
}
