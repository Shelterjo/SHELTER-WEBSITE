<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;

/**
 * One stage of a module's pipeline (TD-FR-01): a setting, so the Franchise Master can change the stages without code.
 *
 * @property int $id
 * @property string $module
 * @property string $code
 * @property string $label_ar
 * @property string $label_en
 * @property int $sort
 * @property bool $is_active
 */
class PipelineStage extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function label(string $locale): string
    {
        return $locale === 'ar' ? $this->label_ar : $this->label_en;
    }
}
