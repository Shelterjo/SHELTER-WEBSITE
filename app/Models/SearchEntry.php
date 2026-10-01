<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row of the derived search index (GLOBAL-SEARCH §3). Never edited by hand: SearchIndexer rebuilds it.
 *
 * @property int $id
 * @property string $entity_type product | category | branch | page | faq
 * @property string $entity_id
 * @property string $scope PUBLIC | OWNER
 * @property string|null $title_ar
 * @property string|null $title_en
 * @property string|null $meta_ar
 * @property string|null $meta_en
 * @property string $normalized_title
 * @property string $normalized_text
 * @property string|null $url_ar
 * @property string|null $url_en
 * @property int $boost
 * @property int $sort
 */
class SearchEntry extends Model
{
    public const UPDATED_AT = 'updated_at';

    public const CREATED_AT = null;

    protected $table = 'search_index';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['boost' => 'integer', 'sort' => 'integer'];
    }
}
