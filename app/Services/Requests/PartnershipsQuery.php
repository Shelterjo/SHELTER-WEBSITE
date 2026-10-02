<?php

namespace App\Services\Requests;

use App\Models\Recruitment\Application;
use App\Services\Recruitment\ApplicantInput;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * The partnerships list for the Owner (docs/franchise/04 §3, FRAN-056/064): search (applicant, phone, email, country,
 * city, market, number), filters (stage, country, date), sort, page size — and the V1 overview (new, total, per stage,
 * the most requested countries / cities / markets as a plain table, §5). Archived applications only when asked for.
 */
final class PartnershipsQuery
{
    public const SORTS = ['newest', 'oldest', 'updated'];

    /**
     * @param  array<string, mixed>  $input
     * @return array{q: string, status: string, country: string, from: string, to: string, sort: string, per: int}
     */
    public static function filters(array $input): array
    {
        $date = fn (string $key): string => is_string($input[$key] ?? null) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $input[$key]) === 1 ? $input[$key] : '';
        $status = is_string($input['status'] ?? null) && in_array($input['status'], ['new', ...ApplicationInbox::statuses('FR')], true) ? $input['status'] : '';
        $country = is_string($input['country'] ?? null) && preg_match('/^[A-Z]{2}$/', $input['country']) === 1 ? $input['country'] : '';

        return [
            'q' => is_string($input['q'] ?? null) ? mb_substr(trim($input['q']), 0, 100) : '',
            'status' => $status,
            'country' => $country,
            'from' => $date('from'),
            'to' => $date('to'),
            'sort' => is_string($input['sort'] ?? null) && in_array($input['sort'], self::SORTS, true) ? $input['sort'] : 'newest',
            'per' => in_array((int) ($input['per'] ?? 0), CareersQuery::PAGE_SIZES, true) ? (int) $input['per'] : 50,
        ];
    }

    /**
     * @param  array{q: string, status: string, country: string, from: string, to: string, sort: string, per: int}  $f
     * @return LengthAwarePaginator<int, Application>
     */
    public function paginate(array $f, int $page = 1): LengthAwarePaginator
    {
        $query = $this->base()->with('partnership');
        match ($f['status']) {
            '' => $query->where('applications.status', '!=', 'archived'),
            'new' => $query->whereNull('applications.first_viewed_at')->where('applications.status', '!=', 'archived'),
            default => $query->where('applications.status', $f['status']),
        };
        if ($f['q'] !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $f['q']).'%';
            $phone = ApplicantInput::phone($f['q']);
            $query->where(function (Builder $w) use ($like, $phone, $f): void {
                $w->where('partnership_applications.full_name', 'like', $like)
                    ->orWhere('partnership_applications.email_normalized', 'like', mb_strtolower($like))
                    ->orWhere('partnership_applications.city_text', 'like', $like)
                    ->orWhere('partnership_applications.market_interest', 'like', $like)
                    ->orWhere('applications.reference_number', 'like', mb_strtoupper($like))
                    ->orWhere('partnership_applications.country_code', mb_strtoupper(trim($f['q'])));
                if ($phone !== null) {
                    $w->orWhere('partnership_applications.phone_normalized', $phone);
                }
            });
        }
        if ($f['country'] !== '') {
            $query->where('partnership_applications.country_code', $f['country']);
        }
        if ($f['from'] !== '') {
            $query->where('applications.submitted_at', '>=', CarbonImmutable::parse($f['from'], 'Asia/Amman')->startOfDay()->utc());
        }
        if ($f['to'] !== '') {
            $query->where('applications.submitted_at', '<=', CarbonImmutable::parse($f['to'], 'Asia/Amman')->endOfDay()->utc());
        }
        match ($f['sort']) {
            'oldest' => $query->orderBy('applications.submitted_at'),
            'updated' => $query->orderByDesc('applications.updated_at'),
            default => $query->orderByDesc('applications.submitted_at'),
        };

        return $query->orderByDesc('applications.id')->paginate($f['per'], ['applications.*'], 'page', $page);
    }

    /**
     * New, total (not archived) and the count per stage.
     *
     * @return array{new: int, total: int, stages: array<string, int>}
     */
    public function counts(): array
    {
        $rows = Application::query()->where('type', 'FR')
            ->selectRaw('status, count(*) as n, sum(case when first_viewed_at is null and status != ? then 1 else 0 end) as unseen', ['archived'])
            ->groupBy('status')->get();
        $stages = array_fill_keys(ApplicationInbox::statuses('FR'), 0);
        $new = $total = 0;
        foreach ($rows as $row) {
            $status = (string) $row->getAttribute('status');
            $n = (int) $row->getAttribute('n');
            $new += (int) $row->getAttribute('unseen');
            $total += $status !== 'archived' ? $n : 0;
            $stages[$status] = ($stages[$status] ?? 0) + $n;
        }

        return ['new' => $new, 'total' => $total, 'stages' => $stages];
    }

    /**
     * The most requested countries, cities and markets (not archived) — a plain table, no charts (§5).
     *
     * @return array{country: array<string, int>, city: array<string, int>, market: array<string, int>}
     */
    public function demand(int $limit = 5): array
    {
        $top = fn (string $column): array => DB::table('partnership_applications')
            ->join('applications', 'applications.id', '=', 'partnership_applications.application_id')
            ->where('applications.status', '!=', 'archived')
            ->select('partnership_applications.'.$column)->selectRaw('count(*) as n')->groupBy('partnership_applications.'.$column)
            ->orderByDesc('n')->orderBy('partnership_applications.'.$column)->limit($limit)
            ->pluck('n', $column)->map(fn (mixed $n): int => (int) $n)->all();

        return ['country' => $top('country_code'), 'city' => $top('city_text'), 'market' => $top('market_interest')];
    }

    /** @return Builder<Application> */
    private function base(): Builder
    {
        return Application::query()->where('applications.type', 'FR')
            ->join('partnership_applications', 'partnership_applications.application_id', '=', 'applications.id');
    }
}
