<?php

namespace App\Services\Dashboard;

use App\Models\Branch;
use App\Models\ContentVersion;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SiteText;
use App\Models\User;
use App\Services\Content\SiteTexts;
use App\Services\Core\AuditLogger;
use App\Services\Core\Settings;
use App\Services\Core\Versions;
use App\Services\Menu\MenuEditor;
use App\Services\Shaltoor\ShaltoorSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

/**
 * Restoring an earlier version (AUDIT-004 "when safe", AUDIT-008, ROLLBACK.md §1 content, RB-T5). The version's
 * content goes back through the item's own save, so it meets the same rules as any save — blocked phrases
 * (ContentGuard), both languages complete on a published page, no archived image, a site text's placeholders — and
 * the same approval path for business facts (a branch's details and the founding year are approved in the Owner's name
 * exactly as an edit does, OwnerApproval/FactRegistry), never written around them. The save makes a NEW version;
 * nothing earlier is changed or removed. Then one audit line "restored from version N" with what changed.
 *
 * Safe = the preview's fingerprint of what is live now still matches (another tab or a newer save means: preview
 * again), the version holds no masked value, a price never lands under a newer price that starts later, and a page
 * keeps its current state (published stays published, a draft stays a draft). Restorable: brand pages, site texts
 * (incl. Google titles and descriptions), the settings the dashboard edits (founding year, Shaltoor), branch details,
 * a product's base price. Other versions are listed, not restored.
 */
final class VersionRestore
{
    /** Settings a restore may change — each has its own editor and rules. */
    public const SETTINGS = ['brand.founded_year', 'shaltoor.enabled', 'shaltoor.welcome.ar', 'shaltoor.welcome.en', 'shaltoor.suggestions.ar', 'shaltoor.suggestions.en'];

    public const PER_PAGE = 20;

    /** The audit action of a restore, by item. */
    private const ACTIONS = [
        Page::class => 'pages.restored',
        SiteText::class => 'texts.restored',
        Setting::class => 'settings.restored',
        Branch::class => 'branch.details_restored',
        Product::class => 'menu.price_restored',
    ];

    private const PAGE_FIELDS = ['title_ar', 'title_en', 'name_ar', 'name_en', 'description_ar', 'description_en'];

    private const SECTION_FIELDS = ['type', 'heading_ar', 'heading_en', 'body_ar', 'body_en', 'is_visible', 'media_id'];

    private const BRANCH_FIELDS = ['name_ar', 'name_en', 'address_ar', 'address_en', 'landmark_ar', 'landmark_en', 'maps_url', 'latitude', 'longitude', 'is_public'];

    /** Longest value kept in the restore's audit line (the full text is in the versions). */
    private const AUDIT_MAX = 500;

    public function __construct(private readonly Versions $versions, private readonly AuditLogger $audit) {}

    /**
     * An item's versions, newest first.
     *
     * @return LengthAwarePaginator<int, ContentVersion>
     */
    public function list(Model $item, int $page): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, ContentVersion> $result */
        $result = ContentVersion::query()->with('user')
            ->where('versionable_type', $item->getMorphClass())->where('versionable_id', $item->getKey())
            ->orderByDesc('version')->paginate(self::PER_PAGE, ['*'], 'page', max(1, $page));

        return $result;
    }

    /** Why this version cannot be restored (dashboard.history.blocked.*), or null when it can. */
    public function blocker(ContentVersion $version): ?string
    {
        $item = $version->versionable;
        if (! $item instanceof Model) {
            return 'missing';
        }
        if (! $this->supports($item, $version->snapshot)) {
            return 'unsupported';
        }

        return self::hasMasked($version->snapshot) ? 'masked' : null;
    }

    /**
     * What is live now and what the version holds, field by field (only the fields the version holds).
     *
     * @return array{now: array<string, mixed>, then: array<string, mixed>}
     */
    public function state(ContentVersion $version): array
    {
        $item = $version->versionable;
        $snap = $version->snapshot;
        $now = [];
        $then = [];
        if ($item instanceof Page) {
            $page = is_array($snap['page'] ?? null) ? $snap['page'] : [];
            foreach (self::PAGE_FIELDS as $field) {
                $now[$field] = $item->getAttribute($field);
                $then[$field] = $page[$field] ?? null;
            }
            $current = $item->sections()->whereNull('archived_at')->get()->values();
            $saved = array_values(array_filter(is_array($snap['sections'] ?? null) ? $snap['sections'] : [], 'is_array'));
            for ($i = 0, $n = max(count($current), count($saved)); $i < $n; $i++) {
                foreach (self::SECTION_FIELDS as $field) {
                    $key = 'sections.'.($i + 1).'.'.$field;
                    $now[$key] = $current->get($i)?->getAttribute($field);
                    $then[$key] = $saved[$i][$field] ?? null;
                }
            }
        } elseif ($item instanceof SiteText) {
            $key = $item->key.'.'.$item->locale;
            $now[$key] = SiteTexts::current($item->key, $item->locale);
            $then[$key] = is_string($snap['value'] ?? null) ? $snap['value'] : SiteTexts::original($item->key, $item->locale);
        } elseif ($item instanceof Setting) {
            $now[$item->key] = app(Settings::class)->get($item->key);
            $then[$item->key] = $snap['value'] ?? null;
        } elseif ($item instanceof Branch) {
            foreach (self::BRANCH_FIELDS as $field) {
                if (array_key_exists($field, $snap)) {
                    $now[$field] = self::branchValue($item, $field);
                    $then[$field] = $snap[$field];
                }
            }
        } elseif ($item instanceof Product) {
            $now['price_fils'] = $item->prices()->whereNull('valid_to')->orderByDesc('valid_from')->value('price_fils');
            $then['price_fils'] = $snap['price_fils'] ?? null;
        }

        return ['now' => $now, 'then' => $then];
    }

    /**
     * The fields the restore would change (an empty list: the version is what is live now).
     *
     * @param  array<string, mixed>  $now
     * @param  array<string, mixed>  $then
     * @return list<string>
     */
    public static function diff(array $now, array $then): array
    {
        $changed = [];
        foreach (array_keys($now + $then) as $field) {
            if (! self::same($now[$field] ?? null, $then[$field] ?? null)) {
                $changed[] = (string) $field;
            }
        }

        return $changed;
    }

    /** What the preview stood for: the item as it is now. */
    public function fingerprint(ContentVersion $version): string
    {
        $now = $this->state($version)['now'];
        ksort($now);

        return hash('sha256', $version->versionable_type.'|'.$version->versionable_id.'|'.json_encode(array_map(self::normal(...), $now)));
    }

    /**
     * Restores the version as a NEW version, through the item's own save and its rules.
     *
     * @return array{errors: list<string>, version: int|null}
     */
    public function restore(ContentVersion $version, User $owner, string $fingerprint, ?string $note = null): array
    {
        $blocked = $this->blocker($version);
        $item = $version->versionable;
        if ($blocked !== null || ! $item instanceof Model) {
            return ['errors' => [(string) __('dashboard.history.blocked.'.($blocked ?? 'missing'))], 'version' => null];
        }
        // One restore of an item at a time (a double click, two tabs).
        $lock = Cache::lock('history:restore:'.$version->versionable_type.':'.$version->versionable_id, 30);
        if (! $lock->get()) {
            return ['errors' => [(string) __('dashboard.history.errors.busy')], 'version' => null];
        }
        try {
            if (! hash_equals($this->fingerprint($version), $fingerprint)) {
                return ['errors' => [(string) __('dashboard.history.errors.changed')], 'version' => null];
            }
            ['now' => $now, 'then' => $then] = $this->state($version);
            $changed = self::diff($now, $then);
            if ($changed === []) {
                return ['errors' => [(string) __('dashboard.history.errors.same')], 'version' => null];
            }
            $note = $note !== null && trim($note) !== '' ? mb_substr(trim($note), 0, 200) : null;
            $reason = (string) __('dashboard.history.restored_reason', ['version' => $version->version]).($note !== null ? ' — '.$note : '');
            $snap = $version->snapshot;
            $errors = match (true) {
                $item instanceof Page => $this->restorePage($item, $snap, $reason, $owner),
                $item instanceof SiteText => $this->restoreText($item, $snap, $reason, $owner),
                $item instanceof Setting => $this->restoreSetting($item, $snap, $reason, $owner),
                $item instanceof Branch => $this->restoreBranch($item, $snap, $reason, $owner),
                $item instanceof Product => $this->restorePrice($item, $snap, $reason, $owner),
                default => [(string) __('dashboard.history.blocked.unsupported')],
            };
            if ($errors !== []) {
                return ['errors' => array_values(array_unique(array_map('strval', $errors))), 'version' => null];
            }
            $item->refresh();
            $after = $this->state($version);
            if (self::diff(array_intersect_key($after['now'], array_flip($changed)), array_intersect_key($then, array_flip($changed))) !== []) {
                return ['errors' => [(string) __('dashboard.history.errors.not_applied')], 'version' => null];
            }
            $latest = $this->versions->latest($item)?->version;
            $cut = fn (mixed $v): mixed => is_string($v) ? mb_strimwidth($v, 0, self::AUDIT_MAX, '…') : $v;
            $this->audit->record(self::ACTIONS[$item::class], $item, [
                'before' => array_map($cut, array_intersect_key($now, array_flip($changed))),
                'after' => array_map($cut, array_intersect_key($then, array_flip($changed))),
            ], ['reason' => $reason, 'from_version' => $version->version, 'new_version' => $latest], actor: $owner);

            return ['errors' => [], 'version' => $latest];
        } finally {
            $lock->release();
        }
    }

    /** @param array<string, mixed> $snap */
    private function supports(Model $item, array $snap): bool
    {
        return match (true) {
            $item instanceof Page => isset(PageEditor::PAGES[$item->key]) && is_array($snap['page'] ?? null),
            $item instanceof SiteText => SiteTexts::listed($item->key) && array_key_exists('value', $snap),
            $item instanceof Setting => in_array($item->key, self::SETTINGS, true) && array_key_exists('value', $snap),
            $item instanceof Branch => is_string($snap['name_ar'] ?? null) && is_string($snap['name_en'] ?? null),
            $item instanceof Product => is_int($snap['price_fils'] ?? null) && $snap['price_fils'] > 0 && ! array_key_exists('override', $snap),
            default => false,
        };
    }

    /**
     * The page as the version had it, in the editor's own form: its sections back in their order (a section archived
     * since then comes back as a new one — the archived row stays as it was), sections added since then removed
     * (archived), and the page's current state kept.
     *
     * @param  array<string, mixed>  $snap
     * @return array<string, string>
     */
    private function restorePage(Page $page, array $snap, string $reason, User $owner): array
    {
        $fields = is_array($snap['page'] ?? null) ? $snap['page'] : [];
        $live = $page->sections()->whereNull('archived_at')->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
        $rows = [];
        $kept = [];
        foreach (array_values(array_filter(is_array($snap['sections'] ?? null) ? $snap['sections'] : [], 'is_array')) as $i => $section) {
            $id = is_numeric($section['id'] ?? null) && in_array((int) $section['id'], $live, true) ? (int) $section['id'] : null;
            if ($id !== null) {
                $kept[] = $id;
            }
            $rows[] = [
                'id' => $id, 'type' => $section['type'] ?? 'text',
                'heading_ar' => $section['heading_ar'] ?? null, 'heading_en' => $section['heading_en'] ?? null,
                'body_ar' => $section['body_ar'] ?? null, 'body_en' => $section['body_en'] ?? null,
                'visible' => ! empty($section['is_visible']) ? '1' : null, 'media_id' => $section['media_id'] ?? null, 'sort' => $i + 1,
            ];
        }
        foreach (array_diff($live, $kept) as $id) {
            $rows[] = ['id' => $id, 'remove' => '1', 'sort' => count($rows) + 1];
        }
        $input = ['status' => $page->status->value, 'sections' => $rows];
        foreach (self::PAGE_FIELDS as $field) {
            $input[$field] = $fields[$field] ?? null;
        }

        return app(PageEditor::class)->save($page->key, $input, $owner, $reason);
    }

    /**
     * The page's whole group of texts as they are now, with this one text from the version (the texts editor saves a
     * group at once; everything else in it stays as it is).
     *
     * @param  array<string, mixed>  $snap
     * @return list<string>
     */
    private function restoreText(SiteText $text, array $snap, string $reason, User $owner): array
    {
        $group = null;
        foreach (SiteTexts::GROUPS as $name => $keys) {
            if (in_array($text->key, $keys, true)) {
                $group = $name;
            }
        }
        if ($group === null) {
            return [(string) __('dashboard.history.blocked.unsupported')];
        }
        $overrides = SiteTexts::overrides();
        $texts = [];
        $labels = [];
        foreach (SiteTexts::GROUPS[$group] as $key) {
            [$file, $path] = explode('.', $key, 2);
            foreach (['ar', 'en'] as $locale) {
                $texts[$key][$locale] = $overrides[$locale][$file][$path] ?? '';
                $labels[SiteTexts::fieldId($key, $locale)] = ChangeHistory::fieldLabel($key.'.'.$locale);
            }
        }
        $texts[$text->key][$text->locale] = is_string($snap['value'] ?? null) ? $snap['value'] : '';
        $result = app(SiteTexts::class)->save($group, ['texts' => $texts], $owner, $reason);
        $errors = [];
        foreach ($result['errors'] as $field => $message) {
            $errors[] = ($labels[$field] ?? $field).': '.$message;
        }

        return $errors;
    }

    /**
     * The founding year goes through Settings (saving it approves it, as on the Settings screen); a Shaltoor setting
     * through Shaltoor's form as it is now, with this one value from the version.
     *
     * @param  array<string, mixed>  $snap
     * @return array<string, string>
     */
    private function restoreSetting(Setting $setting, array $snap, string $reason, User $owner): array
    {
        $value = $snap['value'] ?? null;
        if ($setting->key === 'brand.founded_year') {
            return app(SettingsEditor::class)->save(['founded_year' => is_scalar($value) ? (string) $value : '', 'reason' => $reason], $owner)['errors'];
        }
        $shaltoor = app(ShaltoorSettings::class);
        $settings = app(Settings::class);
        $lines = fn (mixed $list): string => is_array($list) ? implode("\n", array_filter($list, 'is_string')) : '';
        $input = ['enabled' => $shaltoor->enabled() ? '1' : ''];
        foreach (['ar', 'en'] as $locale) {
            $welcome = $settings->get('shaltoor.welcome.'.$locale);
            $input['welcome_'.$locale] = is_string($welcome) ? $welcome : '';
            $input['suggestions_'.$locale] = $lines($settings->get('shaltoor.suggestions.'.$locale));
        }
        $field = substr($setting->key, strlen('shaltoor.'));
        if ($field === 'enabled') {
            $input['enabled'] = $value === true ? '1' : '';
        } elseif (str_starts_with($field, 'welcome.')) {
            $input['welcome_'.substr($field, 8)] = is_string($value) ? $value : '';
        } else {
            $input['suggestions_'.substr($field, 12)] = $lines($value);
        }

        return $shaltoor->save($input, $owner);
    }

    /**
     * A branch's details through the branch editor (each value approved in the Owner's name, as an edit is). A field
     * the version did not have yet keeps its current value; services and payments are not part of a restore.
     *
     * @param  array<string, mixed>  $snap
     * @return array<string, string>
     */
    private function restoreBranch(Branch $branch, array $snap, string $reason, User $owner): array
    {
        $input = ['reason' => $reason];
        foreach (self::BRANCH_FIELDS as $field) {
            $value = array_key_exists($field, $snap) ? $snap[$field] : self::branchValue($branch, $field);
            $input[$field] = $field === 'is_public' ? (in_array($value, ['0', 0, false], true) ? '0' : '1') : (is_scalar($value) ? (string) $value : '');
        }

        return app(BranchEditor::class)->save($branch, $input, $owner);
    }

    /**
     * A base price comes back as a new price from today; the current one stays in the price history. Refused when a
     * newer price is already set to start after today.
     *
     * @param  array<string, mixed>  $snap
     * @return array<string, string>
     */
    private function restorePrice(Product $product, array $snap, string $reason, User $owner): array
    {
        try {
            app(MenuEditor::class)->changeBasePrice($product, (int) $snap['price_fils'], $owner, $reason);
        } catch (InvalidArgumentException) {
            return ['price' => (string) __('dashboard.history.errors.newer_price')];
        }

        return [];
    }

    /** A branch value as the branch editor keeps it in its versions. */
    private static function branchValue(Branch $branch, string $field): ?string
    {
        $value = $branch->getAttribute($field);

        return is_bool($value) ? ($value ? '1' : '0') : ($value === null ? null : (string) $value);
    }

    /** @param array<array-key, mixed> $data */
    private static function hasMasked(array $data): bool
    {
        foreach ($data as $value) {
            if ($value === '[MASKED]' || (is_array($value) && self::hasMasked($value))) {
                return true;
            }
        }

        return false;
    }

    private static function same(mixed $a, mixed $b): bool
    {
        return self::normal($a) === self::normal($b);
    }

    /** One form for comparing: empty = null, numbers and booleans as text, lists as JSON. */
    private static function normal(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return is_scalar($value) ? (string) $value : (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
