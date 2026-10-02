<?php

namespace App\Services\Menu;

use App\Models\MenuCategory;
use App\Models\Product;
use App\Models\User;
use App\Services\Content\Search\Normalizer;
use App\Services\Core\AuditLogger;
use App\Services\Core\Versions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

/**
 * The menu's sections (M50, MENU-039, P-01 — no code): their English name, their Arabic name (shown once approved),
 * shown or hidden (a hidden section's items leave the menu and the search; nothing is deleted), their order, and new
 * sections (the next CAT number). The order starts from the approved one (MENU-039); a change is the Owner's own
 * decision, recorded. The seasonal section keeps its own screen (CMS-009). Every change is versioned and audited.
 */
final class MenuSections
{
    public const HIDDEN = 'hidden';

    public const NAME_MAX = 60;

    private const CHANNELS = ['website.menu', 'schema.menu'];

    public function __construct(private readonly Versions $versions, private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $input  name_en, name_ar, approve_ar, visible (1/0)
     * @return array<string, string> errors; empty = saved
     */
    public function save(MenuCategory $category, array $input, User $owner): array
    {
        self::assertOwner($owner);
        [$values, $errors] = $this->read($input, $category);
        if ($errors !== []) {
            return $errors;
        }
        DB::transaction(function () use ($category, $values, $input, $owner): void {
            $fields = ['name_en', 'name_ar', 'name_ar_status', 'status'];
            $before = $category->only($fields);
            $wasApproved = str_starts_with($category->name_ar_status, 'APPROVED');
            $approve = ! empty($input['approve_ar']) && $values['name_ar'] !== null;
            $status = $category->type === 'seasonal' ? $category->status : (($input['visible'] ?? '1') === '0' ? self::HIDDEN : 'published');
            $category->forceFill([
                'name_en' => $values['name_en'],
                'name_ar' => $approve ? $values['name_ar'] : null,
                'name_ar_status' => match (true) {
                    $approve && (! $wasApproved || $values['name_ar'] !== $category->name_ar) => 'APPROVED — OWNER DASHBOARD',
                    ! $approve && $wasApproved => 'PENDING OWNER REVIEW — APPROVAL WITHDRAWN',
                    default => $category->name_ar_status,
                },
                'status' => $status === 'draft' ? 'published' : $status,
            ])->save();
            $after = $category->only($fields);
            if ($after != $before) {
                $this->versions->record($category, 'published', ['code' => $category->code] + $after, null, $owner);
                $this->audit->record('menu.section_saved', $category, ['before' => array_diff_assoc($before, $after), 'after' => array_diff_assoc($after, $before)], [], self::CHANNELS, $owner);
            }
        });

        return [];
    }

    /**
     * A new section at the end of the menu (the Owner's Arabic name, if typed, is approved).
     *
     * @param  array<string, mixed>  $input
     * @return array{category: MenuCategory|null, errors: array<string, string>}
     */
    public function create(array $input, User $owner): array
    {
        self::assertOwner($owner);
        [$values, $errors] = $this->read($input, null);
        if ($errors !== []) {
            return ['category' => null, 'errors' => $errors];
        }
        $category = DB::transaction(function () use ($values, $owner): MenuCategory {
            $last = MenuCategory::query()->lockForUpdate()->pluck('code')
                ->map(fn (string $code): int => preg_match('/^CAT-(\d+)$/', $code, $m) === 1 ? (int) $m[1] : 0)->max() ?? 0;
            $category = MenuCategory::query()->create([
                'code' => sprintf('CAT-%03d', (int) $last + 1),
                'source_name' => $values['name_en'],
                'name_en' => $values['name_en'],
                'name_ar' => $values['name_ar'],
                'name_ar_status' => $values['name_ar'] !== null ? 'APPROVED — OWNER DASHBOARD' : 'MISSING — OWNER INPUT REQUIRED',
                'type' => 'standard',
                'status' => 'published',
                'sort' => (int) MenuCategory::query()->max('sort') + 1,
            ]);
            $after = $category->only(['code', 'name_en', 'name_ar', 'sort']);
            $this->versions->record($category, 'published', $after, null, $owner);
            $this->audit->record('menu.section_created', $category, ['before' => null, 'after' => $after], [], self::CHANNELS, $owner);

            return $category;
        });

        return ['category' => $category, 'errors' => []];
    }

    /** One place up or down among the regular sections (the season stays on top — MENU-039). */
    public function move(MenuCategory $category, string $direction, User $owner): bool
    {
        self::assertOwner($owner);
        $order = MenuCategory::query()->where('type', '!=', 'seasonal')->orderBy('sort')->orderBy('id')->get()->values();
        $i = $order->search(fn (MenuCategory $c): bool => $c->id === $category->id);
        $j = $i === false ? false : ($direction === 'up' ? $i - 1 : $i + 1);
        if ($i === false || $j === false || $j < 0 || $j >= $order->count()) {
            return false;
        }
        DB::transaction(function () use ($order, $i, $j, $owner): void {
            $list = $order->all();
            [$list[$i], $list[$j]] = [$list[$j], $list[$i]];
            $before = $order->pluck('code')->all();
            foreach (array_values($list) as $position => $c) {
                $c->forceFill(['sort' => $position + 1])->save(); // the season keeps sort 0
            }
            $this->audit->record('menu.sections_ordered', $list[$i], ['before' => $before, 'after' => array_map(fn (MenuCategory $c): string => (string) $c->code, $list)], [], self::CHANNELS, $owner);
        });

        return true;
    }

    /** @return int items still on a section (hidden or not) */
    public static function items(MenuCategory $category): int
    {
        return Product::query()->where('menu_category_id', $category->id)->where('status', Product::STATUS_ACTIVE)->whereNull('merged_into_id')->count();
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{0: array{name_en: string, name_ar: ?string}, 1: array<string, string>}
     */
    private function read(array $input, ?MenuCategory $category): array
    {
        $clean = fn (mixed $v): ?string => is_string($v) && trim($v) !== '' ? trim((string) preg_replace('/\s+/u', ' ', $v)) : null;
        $en = $clean($input['name_en'] ?? null);
        $ar = $clean($input['name_ar'] ?? null);
        $errors = [];
        if ($en === null) {
            $errors['name_en'] = (string) __('dashboard.menu.errors.name_required');
        } elseif (mb_strlen($en) > self::NAME_MAX) {
            $errors['name_en'] = (string) __('dashboard.pages.errors.too_long', ['max' => self::NAME_MAX]);
        } elseif (MenuCategory::query()->when($category !== null, fn ($q) => $q->whereKeyNot($category?->id))->get(['name_en'])
            ->contains(fn (MenuCategory $c): bool => Normalizer::normalize((string) $c->name_en) === Normalizer::normalize($en))) {
            $errors['name_en'] = (string) __('dashboard.menu.sections.errors.duplicate');
        }
        if ($ar !== null && mb_strlen($ar) > self::NAME_MAX) {
            $errors['name_ar'] = (string) __('dashboard.pages.errors.too_long', ['max' => self::NAME_MAX]);
        } elseif ($ar === null && ! empty($input['approve_ar'])) {
            $errors['name_ar'] = (string) __('dashboard.menu.errors.name_required');
        }

        return [['name_en' => (string) $en, 'name_ar' => $ar], $errors];
    }

    private static function assertOwner(User $user): void
    {
        if (! $user->isOwner()) {
            throw new AuthorizationException('Only the owner can change the menu sections.');
        }
    }
}
