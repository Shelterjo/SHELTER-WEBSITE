<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One block of a page from an approved section type (Design lock): `text` = optional heading + paragraphs,
 * `faq` = question (heading) + answer (body), `list` = heading + items, `steps` = heading + ordered steps (one item per
 * line — e.g. the franchise criteria and partnership journey). Plain text only — rendered escaped.
 *
 * @property int|null $media_id
 * @property int $id
 * @property int $page_id
 * @property string $type text | faq
 * @property string|null $heading_ar
 * @property string|null $heading_en
 * @property string|null $body_ar
 * @property string|null $body_en
 * @property string $origin owner | import | ai
 * @property bool $is_visible
 * @property int $sort
 * @property Carbon|null $archived_at
 */
class PageSection extends Model
{
    public const TYPES = ['text', 'faq', 'list', 'steps', 'cards', 'cta'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'sort' => 'integer', 'archived_at' => 'datetime'];
    }

    /** @return BelongsTo<Page, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function heading(string $locale): ?string
    {
        $value = $locale === 'ar' ? $this->heading_ar : $this->heading_en;

        return filled($value) ? (string) $value : null;
    }

    public function body(string $locale): ?string
    {
        $value = $locale === 'ar' ? $this->body_ar : $this->body_en;

        return filled($value) ? (string) $value : null;
    }

    /** @return BelongsTo<Media, $this> an approved image (only drawn while MediaRights allows it) */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
