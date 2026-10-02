<?php

namespace App\Services\Dashboard;

use App\Enums\PublishStatus;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\User;
use App\Services\Content\Pages;
use App\Services\Content\Search\SearchIndexer;
use App\Services\Core\AuditLogger;
use App\Services\Core\Versions;
use App\Services\Media\MediaRights;
use Illuminate\Support\Facades\DB;

/**
 * The Owner's page editor (no code — M30, M50): every fixed brand page, its title, name and description, and its
 * sections in both languages. Publishing applies the same rules the site does (Pages): title in both languages and
 * every visible section complete in both, no blocked commercial phrase — otherwise the save is refused with the exact
 * reason, so a live page never silently disappears. A draft may stay incomplete. Removing a section archives it.
 * Every save keeps a full version (Versions) and an audit entry, and refreshes site search.
 */
final class PageEditor
{
    /** Editable fixed pages: key => page type (route = key). */
    public const PAGES = ['about' => 'brand', 'faq' => 'faq', 'franchise' => 'landing', 'media' => 'brand', 'privacy' => 'legal', 'terms' => 'legal'];

    private const LIMITS = ['title' => 255, 'name' => 120, 'description' => 300, 'heading' => 255, 'body' => 20000];

    public function __construct(
        private readonly Versions $versions,
        private readonly AuditLogger $audit,
        private readonly SearchIndexer $search,
    ) {}

    /** The stored page, or a new unsaved draft for a fixed key. */
    public function page(string $key): Page
    {
        return Page::query()->with(['sections' => fn ($q) => $q->whereNull('archived_at')])->where('key', $key)->first()
            ?? new Page(['key' => $key, 'type' => self::PAGES[$key], 'status' => PublishStatus::Draft]);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string> errors (field => message); empty = saved
     */
    public function save(string $key, array $input, User $owner): array
    {
        $text = fn (mixed $value): ?string => is_string($value) && trim($value) !== '' ? trim(str_replace(["\r\n", "\r"], "\n", $value)) : null;
        $status = ($input['status'] ?? '') === 'published' ? PublishStatus::Published : PublishStatus::Draft;
        $fields = [];
        foreach (['title', 'name', 'description'] as $field) {
            foreach (['ar', 'en'] as $locale) {
                $fields[$field.'_'.$locale] = $text($input[$field.'_'.$locale] ?? null);
            }
        }

        $errors = [];
        foreach ($fields as $field => $value) {
            $limit = self::LIMITS[explode('_', $field)[0]];
            if ($value !== null && mb_strlen($value) > $limit) {
                $errors[$field] = (string) __('dashboard.pages.errors.too_long', ['max' => $limit]);
            }
        }

        // Rows keep their form key (the card's id on screen, so an error links to the right card) and their position
        // on screen (the number the Owner sees); the order field decides the saved order.
        $rows = [];
        $position = 0;
        foreach (is_array($input['sections'] ?? null) ? $input['sections'] : [] as $formKey => $row) {
            if (! is_array($row) || ! ctype_digit((string) $formKey)) {
                continue;
            }
            $index = (int) $formKey;
            $position++;
            $section = [
                'id' => isset($row['id']) && ctype_digit((string) $row['id']) ? (int) $row['id'] : null,
                'type' => in_array($row['type'] ?? null, PageSection::TYPES, true) ? $row['type'] : 'text',
                'heading_ar' => $text($row['heading_ar'] ?? null), 'heading_en' => $text($row['heading_en'] ?? null),
                'body_ar' => $text($row['body_ar'] ?? null), 'body_en' => $text($row['body_en'] ?? null),
                'is_visible' => ! empty($row['visible']),
                'media_id' => is_numeric($row['media_id'] ?? null) ? (int) $row['media_id'] : null,
                'remove' => ! empty($row['remove']),
                'order' => is_numeric($row['sort'] ?? null) ? (float) $row['sort'] : $position,
                'index' => $index,
                'position' => $position,
            ];
            $empty = $section['heading_ar'] === null && $section['heading_en'] === null && $section['body_ar'] === null && $section['body_en'] === null;
            if ($section['id'] === null && ($empty || $section['remove'])) {
                continue; // an untouched blank slot
            }
            if ($section['media_id'] !== null && ! MediaRights::canUse(Media::query()->find($section['media_id']))) {
                $errors["sections.{$index}.media_id"] = (string) __('dashboard.awards.errors.image');
            }
            foreach (['heading', 'body'] as $part) {
                foreach (['ar', 'en'] as $locale) {
                    $value = $section[$part.'_'.$locale];
                    if ($value !== null && mb_strlen($value) > self::LIMITS[$part]) {
                        $errors["sections.{$index}.{$part}_{$locale}"] = (string) __('dashboard.pages.errors.too_long', ['max' => self::LIMITS[$part]]);
                    }
                }
            }
            $rows[] = $section;
        }
        usort($rows, fn (array $a, array $b): int => [$a['order'], $a['position']] <=> [$b['order'], $b['position']]);

        if ($status === PublishStatus::Published) {
            foreach (['title_ar', 'title_en'] as $field) {
                if ($fields[$field] === null) {
                    $errors[$field] = (string) __('dashboard.pages.errors.title_required');
                }
            }
            $live = array_filter($rows, fn (array $s): bool => $s['is_visible'] && ! $s['remove']);
            if ($live === []) {
                $errors['sections'] = (string) __('dashboard.pages.errors.no_sections');
            }
            foreach ($live as $section) {
                $problem = $this->problem($section);
                if ($problem !== null) {
                    $errors["sections.{$section['index']}"] = (string) __('dashboard.pages.errors.section', ['n' => $section['position'], 'problem' => $problem]);
                }
            }
            $parts = array_values($fields);
            foreach ($live as $section) {
                array_push($parts, $section['heading_ar'], $section['heading_en'], $section['body_ar'], $section['body_en']);
            }
            $phrase = Pages::blockedPhrase($key, implode("\n", array_filter($parts)));
            if ($phrase !== null) {
                $errors['blocked'] = (string) __('dashboard.pages.errors.blocked', ['phrase' => $phrase]);
            }
        }
        if ($errors !== []) {
            return $errors;
        }

        DB::transaction(function () use ($key, $fields, $status, $rows, $owner): void {
            $page = Page::query()->where('key', $key)->first() ?? new Page(['key' => $key]);
            $wasPublished = $page->exists && $page->status === PublishStatus::Published;
            $page->fill($fields + [
                'type' => self::PAGES[$key],
                'status' => $status,
                'origin' => 'owner',
                'content_updated_at' => now(),
            ]);
            if ($status === PublishStatus::Published && $page->published_at === null) {
                $page->published_at = now();
            }
            $page->save();

            $sort = 0;
            foreach ($rows as $row) {
                $section = $row['id'] !== null ? $page->sections()->whereKey($row['id'])->first() : null;
                if ($row['remove']) {
                    $section?->forceFill(['archived_at' => now(), 'is_visible' => false])->save();

                    continue;
                }
                $values = ['type' => $row['type'], 'heading_ar' => $row['heading_ar'], 'heading_en' => $row['heading_en'],
                    'body_ar' => $row['body_ar'], 'body_en' => $row['body_en'], 'is_visible' => $row['is_visible'], 'media_id' => $row['media_id'], 'origin' => 'owner', 'sort' => ++$sort];
                $section !== null ? $section->update($values) : $page->sections()->create($values);
            }

            $page->load(['sections' => fn ($q) => $q->whereNull('archived_at')]);
            $this->versions->record($page, $status->value, [
                'page' => $page->only(['key', 'type', 'title_ar', 'title_en', 'name_ar', 'name_en', 'description_ar', 'description_en']),
                'sections' => $page->sections->map(fn (PageSection $s): array => $s->only(['id', 'type', 'heading_ar', 'heading_en', 'body_ar', 'body_en', 'is_visible', 'media_id', 'sort']))->all(),
            ], 'dashboard', $owner);
            $this->audit->record('pages.saved', $page, ['after' => ['key' => $key, 'status' => $status->value, 'was_published' => $wasPublished]], actor: $owner);
        });
        $this->search->rebuild();

        return [];
    }

    /**
     * Why a visible section cannot be published yet — the site's own rule (Pages::sectionComplete), in words.
     *
     * @param  array<string, mixed>  $section
     */
    private function problem(array $section): ?string
    {
        $model = new PageSection(array_intersect_key($section, array_flip(['type', 'heading_ar', 'heading_en', 'body_ar', 'body_en'])) + ['origin' => 'owner']);
        if (Pages::sectionComplete($model)) {
            return null;
        }

        return (string) __(match (true) {
            $section['body_ar'] === null => 'dashboard.pages.problems.body_ar',
            $section['body_en'] === null => 'dashboard.pages.problems.body_en',
            $section['type'] === 'faq' && ($section['heading_ar'] === null || $section['heading_en'] === null) => 'dashboard.pages.problems.question',
            default => 'dashboard.pages.problems.heading',
        });
    }
}
