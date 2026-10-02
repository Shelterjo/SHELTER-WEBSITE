<?php

namespace App\Models;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A brand content page with a fixed key and URL (about, faq, privacy, terms, franchise — SITE-INVENTORY SI-B03…B07,
 * SI-B12). Public only through App\Services\Content\Pages, which requires `published`, both languages and no
 * unconfirmed AI text. `title` is the H1; `name` (optional) is the page name used for <title>, breadcrumb and links.
 *
 * @property int $id
 * @property string $key
 * @property string $type brand | faq | legal | landing
 * @property string|null $title_ar
 * @property string|null $title_en
 * @property string|null $name_ar
 * @property string|null $name_en
 * @property string|null $description_ar
 * @property string|null $description_en
 * @property PublishStatus $status
 * @property string $origin owner | import | ai
 * @property Carbon|null $published_at
 * @property Carbon|null $content_updated_at
 * @property Carbon|null $archived_at
 */
class Page extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'content_updated_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /** @return HasMany<PageSection, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort')->orderBy('id');
    }
}
