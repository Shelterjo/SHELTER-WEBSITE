<?php

namespace App\Services\Dashboard;

use App\Enums\Availability;
use App\Models\Branch;
use App\Models\MenuCategory;
use App\Models\Product;
use App\Models\User;
use App\Services\Menu\MenuCatalog;
use App\Services\Menu\MenuEditor;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * The Owner's menu screens (no code — M50, M33 §5, §16–§18, Menu IA §9.6): reads the dashboard forms and hands the
 * changes to MenuEditor (owner only, versioned, audited). Prices are typed in dinars with at most two decimals, so the
 * site shows exactly what was typed; a new base price starts on a date and the old one stays in the history. Each
 * branch can have its own price and availability, or follow the base ("Reset to Master"). Arabic names reach the site
 * only when the Owner approves them (D-091, D-137) — one by one or a whole category at once.
 */
final class MenuManager
{
    private const REASON_MAX = 300;

    private const NAME_MAX = 120;

    /** A sanity range for a menu price (a guard against typos, not a business rule): 0.05 … 100.00 JD. */
    private const MIN_FILS = 50;

    private const MAX_FILS = 100000;

    public const BRANCH_STATES = ['inherit', 'available', 'unavailable_show', 'unavailable_hide'];

    public function __construct(private readonly MenuEditor $editor, private readonly MenuCatalog $catalog) {}

    /** "2.5", "2.50", "٢٫٥٠" → 2500 fils; null when it is not a price with at most two decimals. */
    public static function fils(mixed $value): ?int
    {
        if (! is_string($value)) {
            return null;
        }
        $value = strtr(trim($value), ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', '٫' => '.', ',' => '.']);
        if (preg_match('/^(\d{1,3})(?:\.(\d{1,2}))?$/', $value, $m) !== 1) {
            return null;
        }
        $fils = (int) $m[1] * 1000 + (int) str_pad($m[2] ?? '0', 2, '0') * 10;

        return $fils >= self::MIN_FILS && $fils <= self::MAX_FILS ? $fils : null;
    }

    public static function dinars(int $fils): string
    {
        return number_format($fils / 1000, 2, '.', '');
    }

    /**
     * A new base price from a date (today by default; never in the past).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string> errors; empty = saved
     */
    public function changePrice(Product $product, array $input, User $owner, ?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::now('Asia/Amman')->startOfDay();
        $errors = [];
        $fils = self::fils($input['price'] ?? null);
        if ($fils === null) {
            $errors['price'] = (string) __('dashboard.menu.errors.price', ['min' => self::dinars(self::MIN_FILS), 'max' => self::dinars(self::MAX_FILS)]);
        }
        $from = self::date($input['starts_on'] ?? null) ?? $today;
        if ($from->lessThan($today)) {
            $errors['starts_on'] = (string) __('dashboard.menu.errors.past');
        }
        $current = $this->catalog->basePrice($product, $from);
        if ($fils !== null && $current !== null && $current->price_fils === $fils) {
            $errors['price'] = (string) __('dashboard.menu.errors.same_price');
        }
        $reason = self::reason($input, $errors);
        if ($errors !== [] || $fils === null) {
            return $errors;
        }
        try {
            $this->editor->changeBasePrice($product, $fils, $owner, $reason, $from);
        } catch (InvalidArgumentException) {
            return ['starts_on' => (string) __('dashboard.menu.errors.newer_price')];
        }

        return [];
    }

    /**
     * One branch: its own price (empty = the base price) and availability (inherit = the base).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string> errors; empty = saved
     */
    public function saveBranch(Product $product, Branch $branch, array $input, User $owner): array
    {
        $errors = [];
        $raw = is_string($input['price'] ?? null) ? trim($input['price']) : '';
        $fils = $raw === '' ? null : self::fils($raw);
        if ($raw !== '' && $fils === null) {
            $errors['price'] = (string) __('dashboard.menu.errors.price', ['min' => self::dinars(self::MIN_FILS), 'max' => self::dinars(self::MAX_FILS)]);
        }
        $state = is_string($input['availability'] ?? null) ? $input['availability'] : 'inherit';
        if (! in_array($state, self::BRANCH_STATES, true)) {
            $errors['availability'] = (string) __('dashboard.menu.errors.state');
        }
        $reason = self::reason($input, $errors);
        if ($errors !== []) {
            return $errors;
        }
        if ($fils !== null && $fils === $this->catalog->basePrice($product)?->price_fils) {
            $fils = null; // the same as the base price = no branch price
        }
        $this->editor->setBranchValues($product, $branch, $owner, $reason, $fils, $state === 'inherit' ? null : Availability::from($state));

        return [];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string> errors; empty = saved
     */
    public function saveNames(Product $product, array $input, User $owner): array
    {
        $errors = [];
        $en = self::name($input['name_en'] ?? null);
        $ar = self::name($input['name_ar'] ?? null);
        $approve = ! empty($input['approve_ar']);
        if ($en === null) {
            $errors['name_en'] = (string) __('dashboard.menu.errors.name_required');
        }
        if ($approve && $ar === null) {
            $errors['name_ar'] = (string) __('dashboard.menu.errors.name_required');
        }
        foreach (['name_en' => $en, 'name_ar' => $ar] as $key => $value) {
            if ($value !== null && mb_strlen($value) > self::NAME_MAX) {
                $errors[$key] = (string) __('dashboard.pages.errors.too_long', ['max' => self::NAME_MAX]);
            }
        }
        if ($errors !== [] || $en === null) {
            return $errors;
        }
        $this->editor->setNames($product, $owner, $en, $ar, $approve);

        return [];
    }

    /**
     * Reviews the Arabic names of one category at once (D-137): each ticked row is approved with the name in its box.
     *
     * @param  array<string, mixed>  $input  names[product id] => text, approve[product id] => 1
     * @return array{approved: int, errors: array<string, string>}
     */
    public function reviewCategory(MenuCategory $category, array $input, User $owner): array
    {
        $names = is_array($input['names'] ?? null) ? $input['names'] : [];
        $ticked = array_map('intval', array_keys(array_filter(is_array($input['approve'] ?? null) ? $input['approve'] : [])));
        $products = Product::query()->where('menu_category_id', $category->id)->whereIn('id', $ticked)->get();
        $errors = [];
        foreach ($products as $product) {
            $name = self::name($names[$product->id] ?? null);
            if ($name === null || mb_strlen($name) > self::NAME_MAX) {
                $errors['names.'.$product->id] = (string) __('dashboard.menu.errors.review_row', ['name' => $product->display_name_en]);
            }
        }
        if ($errors !== []) {
            return ['approved' => 0, 'errors' => $errors];
        }
        DB::transaction(function () use ($products, $names, $owner): void {
            foreach ($products as $product) {
                $this->editor->setNames($product, $owner, (string) $product->display_name_en, self::name($names[$product->id] ?? null), true);
            }
        });

        return ['approved' => $products->count(), 'errors' => []];
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, string>  $errors
     */
    private static function reason(array $input, array &$errors): string
    {
        $reason = is_string($input['reason'] ?? null) ? trim($input['reason']) : '';
        if ($reason === '' || mb_strlen($reason) > self::REASON_MAX) {
            $errors['reason'] = (string) __('dashboard.hours.errors.reason');
        }

        return $reason;
    }

    private static function name(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));

        return $value === '' ? null : $value;
    }

    private static function date(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', trim($value), $m) !== 1 || ! checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return null;
        }

        return CarbonImmutable::create((int) $m[1], (int) $m[2], (int) $m[3], 0, 0, 0, 'Asia/Amman');
    }
}
