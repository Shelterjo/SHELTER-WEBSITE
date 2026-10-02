<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;

/**
 * The national ID or passport/document number of one application, AES-256-GCM encrypted at application level with
 * an HMAC-SHA256 blind index for duplicate detection (RECRUITMENT-DATA-MODEL §2.2, RECRUITMENT-SECURITY §5).
 * Never sent to analytics, logs, URLs or error messages; shown masked (********1234) by default.
 *
 * @property int $application_id
 * @property string $id_type national_id | passport_or_document
 * @property string $id_ciphertext
 * @property string $id_nonce
 * @property int $id_key_version
 * @property string $id_last4
 * @property string $id_blind_index
 */
class ApplicationIdentity extends Model
{
    protected $table = 'application_identity_secure';

    protected $primaryKey = 'application_id';

    public $incrementing = false;

    protected $guarded = [];

    protected $hidden = ['id_ciphertext', 'id_nonce', 'id_blind_index'];

    protected function casts(): array
    {
        return ['id_key_version' => 'integer'];
    }
}
