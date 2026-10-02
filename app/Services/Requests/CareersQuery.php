<?php

namespace App\Services\Requests;

use App\Models\Recruitment\Application;
use App\Services\Recruitment\ApplicantInput;
use App\Services\Recruitment\ApplicationValidator;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * The careers list for the Owner (CAREERS-052…064): quick search (name, phone, email, number, job, city — never the
 * identity number, CAREERS-058), the combinable filters, six sorts, 25/50/100 per page, and the overview counts for a
 * period (Jordan time, no comparisons). Archived applications appear only when asked for.
 */
final class CareersQuery
{
    public const SORTS = ['newest', 'oldest', 'salary_desc', 'salary_asc', 'experience_desc', 'experience_asc'];

    public const PAGE_SIZES = [25, 50, 100];

    public const PERIODS = ['today', 'week', 'days30', 'month', 'all'];

    /** Overview cards, in order: new, total, then each status (CAREERS-052). */
    public const CARDS = ['new', 'total', 'under_review', 'interview_shortlisted', 'interviewed', 'accepted', 'rejected', 'archived'];

    private const EXPERIENCE_ORDER = ['none', 'lt1', 'y1_2', 'y3_5', 'y6_10', 'gt10'];

    /**
     * The filters as understood from the request (unknown values dropped).
     *
     * @param  array<string, mixed>  $input
     * @return array{q: string, status: string, city: int|null, gender: string, nationality: string, education: string, experience: string, job: string, from: string, to: string, sort: string, per: int, period: string}
     */
    public static function filters(array $input): array
    {
        $pick = fn (string $key, array $allowed): string => is_string($input[$key] ?? null) && in_array($input[$key], $allowed, true) ? $input[$key] : '';
        $date = fn (string $key): string => is_string($input[$key] ?? null) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $input[$key]) === 1 ? $input[$key] : '';
        $options = ApplicationValidator::OPTIONS;

        return [
            'q' => is_string($input['q'] ?? null) ? mb_substr(trim($input['q']), 0, 100) : '',
            'status' => $pick('status', ['new', ...ApplicationInbox::statuses('JOB')]),
            'city' => is_numeric($input['city'] ?? null) ? (int) $input['city'] : null,
            'gender' => $pick('gender', $options['gender']),
            'nationality' => $pick('nationality', $options['nationality_type']),
            'education' => $pick('education', $options['education_level']),
            'experience' => $pick('experience', $options['experience_band']),
            'job' => is_string($input['job'] ?? null) ? mb_substr(trim($input['job']), 0, 100) : '',
            'from' => $date('from'),
            'to' => $date('to'),
            'sort' => $pick('sort', self::SORTS) ?: 'newest',
            'per' => in_array((int) ($input['per'] ?? 0), self::PAGE_SIZES, true) ? (int) $input['per'] : 50,
            'period' => $pick('period', self::PERIODS) ?: 'month',
        ];
    }

    /**
     * @param  array{q: string, status: string, city: int|null, gender: string, nationality: string, education: string, experience: string, job: string, from: string, to: string, sort: string, per: int, period: string}  $f
     * @return LengthAwarePaginator<int, Application>
     */
    public function paginate(array $f, int $page = 1): LengthAwarePaginator
    {
        return $this->filtered($f)->paginate($f['per'], ['applications.*'], 'page', $page);
    }

    /**
     * The applications the filters select, in their sort order (the list and the export read the same answer).
     *
     * @param  array{q: string, status: string, city: int|null, gender: string, nationality: string, education: string, experience: string, job: string, from: string, to: string, sort: string, per: int, period: string}  $f
     * @return Builder<Application>
     */
    public function filtered(array $f): Builder
    {
        $query = $this->base()->select('applications.*')->with(['job.city']);
        match ($f['status']) {
            '' => $query->where('applications.status', '!=', 'archived'),
            'new' => $query->whereNull('applications.first_viewed_at')->where('applications.status', '!=', 'archived'),
            default => $query->where('applications.status', $f['status']),
        };
        if ($f['q'] !== '') {
            $this->search($query, $f['q']);
        }
        foreach (['gender' => 'gender', 'nationality' => 'nationality_type', 'education' => 'education_level', 'experience' => 'experience_band'] as $key => $column) {
            if ($f[$key] !== '') {
                $query->where('job_applications.'.$column, $f[$key]);
            }
        }
        if ($f['city'] !== null) {
            $query->where('job_applications.city_id', $f['city']);
        }
        if ($f['job'] !== '') {
            $query->where('job_applications.job_title_text', 'like', '%'.self::like($f['job']).'%');
        }
        if ($f['from'] !== '') {
            $query->where('applications.submitted_at', '>=', CarbonImmutable::parse($f['from'], 'Asia/Amman')->startOfDay()->utc());
        }
        if ($f['to'] !== '') {
            $query->where('applications.submitted_at', '<=', CarbonImmutable::parse($f['to'], 'Asia/Amman')->endOfDay()->utc());
        }
        $experience = 'CASE job_applications.experience_band '.implode(' ', array_map(fn (string $band, int $i): string => "WHEN '{$band}' THEN {$i}", self::EXPERIENCE_ORDER, array_keys(self::EXPERIENCE_ORDER))).' END';
        match ($f['sort']) {
            'oldest' => $query->orderBy('applications.submitted_at'),
            'salary_desc' => $query->orderByDesc('job_applications.expected_salary_jod'),
            'salary_asc' => $query->orderBy('job_applications.expected_salary_jod'),
            'experience_desc' => $query->orderByRaw($experience.' DESC'),
            'experience_asc' => $query->orderByRaw($experience.' ASC'),
            default => $query->orderByDesc('applications.submitted_at'),
        };
        $query->orderByDesc('applications.id');

        return $query;
    }

    /**
     * Only job applications, by id — what a selection may contain.
     *
     * @param  array<int|string, mixed>  $ids
     * @return list<int>
     */
    public static function jobIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', array_filter($ids, 'is_numeric'))));

        return $ids === [] ? [] : array_values(Application::query()->where('type', 'JOB')->whereIn('id', $ids)->pluck('id')->map(fn ($id): int => (int) $id)->all());
    }

    /**
     * The overview cards for a period (CAREERS-052/053).
     *
     * @return array<string, int>
     */
    public function counts(string $period, ?CarbonImmutable $now = null): array
    {
        $now = ($now ?? CarbonImmutable::now())->setTimezone('Asia/Amman');
        $since = match ($period) {
            'today' => $now->startOfDay(),
            'week' => $now->subDays(6)->startOfDay(),
            'days30' => $now->subDays(29)->startOfDay(),
            'all' => null,
            default => $now->startOfMonth(),
        };
        $rows = Application::query()->where('type', 'JOB')->when($since !== null, fn (Builder $q) => $q->where('submitted_at', '>=', $since?->utc()))
            ->selectRaw('status, count(*) as n, sum(case when first_viewed_at is null and status != ? then 1 else 0 end) as unseen', ['archived'])
            ->groupBy('status')->get();
        $counts = array_fill_keys(self::CARDS, 0);
        foreach ($rows as $row) {
            $status = (string) $row->getAttribute('status');
            $n = (int) $row->getAttribute('n');
            $counts['new'] += (int) $row->getAttribute('unseen');
            if ($status !== 'archived') {
                $counts['total'] += $n;
            }
            if (array_key_exists($status, $counts)) {
                $counts[$status] += $n;
            }
        }

        return $counts;
    }

    /** @return Builder<Application> */
    private function base(): Builder
    {
        return Application::query()->where('applications.type', 'JOB')
            ->join('job_applications', 'job_applications.application_id', '=', 'applications.id')
            ->leftJoin('jordan_cities', 'jordan_cities.id', '=', 'job_applications.city_id');
    }

    /** @param  Builder<Application>  $query */
    private function search(Builder $query, string $q): void
    {
        $like = '%'.self::like($q).'%';
        $digits = (string) preg_replace('/\D/', '', ApplicantInput::digits($q));
        $phone = ApplicantInput::phone($q);
        $query->where(function (Builder $w) use ($like, $q, $digits, $phone): void {
            $w->where('job_applications.full_name', 'like', $like)
                ->orWhere('job_applications.job_title_text', 'like', $like)
                ->orWhere('job_applications.email_normalized', 'like', mb_strtolower($like))
                ->orWhere('applications.reference_number', 'like', mb_strtoupper($like))
                ->orWhere('jordan_cities.name_ar', 'like', $like)
                ->orWhere('jordan_cities.name_en', 'like', $like);
            if ($phone !== null) {
                $w->orWhere('job_applications.phone_normalized', $phone);
            } elseif (strlen($digits) >= 4 && strlen($digits) === strlen(trim($q))) {
                $w->orWhere('job_applications.phone_normalized', 'like', '%'.$digits.'%');
            }
        });
    }

    private static function like(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
