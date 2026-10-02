<?php

namespace App\Services\Requests;

use App\Models\User;
use App\Services\Dashboard\Preferences;

/**
 * How the Owner sees the applications list (CAREERS-061/062/063): the columns (shown/hidden, order — the name always
 * first), the density and the rows per page — remembered per account, with a reset to the default.
 */
final class CareersView
{
    /** The seven default columns (CAREERS-056), then the ones the Owner may add. */
    public const DEFAULT_COLUMNS = ['job', 'city', 'experience', 'salary', 'date', 'status'];

    public const OPTIONAL_COLUMNS = ['reference', 'phone', 'email', 'education', 'gender', 'nationality', 'updated'];

    public const DENSITIES = ['comfortable', 'compact'];

    public function __construct(private readonly Preferences $preferences) {}

    /** @return list<string> visible columns after the name, in order */
    public function columns(User $owner): array
    {
        $saved = $this->preferences->get($owner, 'careers.columns');
        $all = [...self::DEFAULT_COLUMNS, ...self::OPTIONAL_COLUMNS];
        if (! is_array($saved)) {
            return self::DEFAULT_COLUMNS;
        }

        return array_values(array_unique(array_filter($saved, fn ($c): bool => is_string($c) && in_array($c, $all, true))));
    }

    /** @return list<string> every column in the Owner's order: the visible ones first, then the hidden ones */
    public function ordered(User $owner): array
    {
        $visible = $this->columns($owner);

        return [...$visible, ...array_values(array_diff([...self::DEFAULT_COLUMNS, ...self::OPTIONAL_COLUMNS], $visible))];
    }

    public function density(User $owner): string
    {
        $value = $this->preferences->get($owner, 'careers.density');

        return in_array($value, self::DENSITIES, true) ? (string) $value : 'comfortable';
    }

    public function perPage(User $owner): int
    {
        $value = (int) $this->preferences->get($owner, 'careers.per', 50);

        return in_array($value, CareersQuery::PAGE_SIZES, true) ? $value : 50;
    }

    public function rememberPerPage(User $owner, int $per): void
    {
        if (in_array($per, CareersQuery::PAGE_SIZES, true) && $per !== $this->perPage($owner)) {
            $this->preferences->set($owner, 'careers.per', $per);
        }
    }

    /**
     * Applies the "table view" form: shown columns, a move (column:up / column:down) or a dragged order, the density, or a reset.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $owner, array $input): void
    {
        if (! empty($input['reset'])) {
            $this->preferences->forget($owner, 'careers.columns');
            $this->preferences->forget($owner, 'careers.density');

            return;
        }
        $order = $this->ordered($owner);
        $submitted = array_map('strval', is_array($input['shown'] ?? null) ? $input['shown'] : []);
        // After a drag (CAREERS-061) the ticked columns arrive in their new order; otherwise the saved order stays.
        $shown = ! empty($input['reorder'])
            ? array_values(array_unique(array_intersect($submitted, $order)))
            : array_values(array_intersect($order, $submitted));
        $move = is_string($input['move'] ?? null) ? explode(':', $input['move']) : [];
        if (count($move) === 2 && in_array($move[0], $shown, true)) {
            $i = (int) array_search($move[0], $shown, true);
            $j = $move[1] === 'up' ? $i - 1 : $i + 1;
            if ($j >= 0 && $j < count($shown)) {
                [$shown[$i], $shown[$j]] = [$shown[$j], $shown[$i]];
            }
        }
        $this->preferences->set($owner, 'careers.columns', $shown);
        if (in_array($input['density'] ?? null, self::DENSITIES, true)) {
            $this->preferences->set($owner, 'careers.density', $input['density']);
        }
    }
}
