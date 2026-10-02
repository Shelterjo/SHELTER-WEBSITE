<?php

namespace App\Services\Core;

use App\Enums\Priority;
use App\Enums\Severity;
use App\Enums\SignalCategory;
use App\Enums\SignalKind;
use App\Models\Branch;
use App\Models\Fact;
use App\Models\Media;
use App\Models\Signal;
use Illuminate\Support\Facades\Route;

/**
 * "Needs attention" (MON-007, DASH-017, OPS-048): the open ISSUE signals the Owner must see — a value hidden because
 * it changed without approval, a scheduled task that failed, an image whose rights end soon — most serious first, each
 * with the screen that fixes it. An issue closes itself when its cause is fixed (approval, a successful run, renewed
 * rights); only information can be dismissed (M35 §8).
 */
final class Attention
{
    private const RANK = ['CRITICAL' => 0, 'HIGH' => 1, 'MEDIUM' => 2, 'LOW' => 3, 'INFO' => 4];

    /** Days before an image's rights end that the Owner is told (MEDIA-RIGHTS). */
    public const RIGHTS_NOTICE_DAYS = 30;

    public function __construct(private readonly Signals $signals) {}

    public function count(): int
    {
        return Signal::query()->open()->where('kind', SignalKind::Issue->value)->count();
    }

    /** @return list<array{signal: Signal, title: string, href: string|null, dismissible: bool}> */
    public function open(string $locale, ?int $limit = null): array
    {
        $signals = Signal::query()->open()->where('kind', SignalKind::Issue->value)->with('subject')->get()
            ->sortBy(fn (Signal $s): array => [self::RANK[$s->severity->value] ?? 9, -($s->last_seen_at?->getTimestamp() ?? 0)])
            ->values();
        if ($limit !== null) {
            $signals = $signals->take($limit);
        }

        return array_values($signals->map(fn (Signal $s): array => [
            'signal' => $s,
            'title' => $locale === 'ar' ? $s->title_ar : $s->title_en,
            'href' => $this->href($s),
            'dismissible' => $s->priority === Priority::Information,
        ])->all());
    }

    /** The daily monitors that feed this list (scheduled through JobRuns). */
    public function runMonitors(): string
    {
        return 'media rights: '.$this->mediaRights();
    }

    /** Approved images whose usage rights end within the notice window; renewed or archived ones close their issue. */
    private function mediaRights(): int
    {
        $soon = now()->addDays(self::RIGHTS_NOTICE_DAYS);
        $raised = 0;
        Media::query()->whereNotNull('rights_expires_at')->get()->each(function (Media $media) use ($soon, &$raised): void {
            $key = "media:{$media->id}:rights";
            if ($media->archived_at === null && $media->rights_expires_at !== null && $media->rights_expires_at->lessThanOrEqualTo($soon)) {
                $date = $media->rights_expires_at->timezone('Asia/Amman')->format('Y-m-d');
                $this->signals->raise(
                    SignalKind::Issue, SignalCategory::Content, Severity::Medium, Priority::Important, 'monitors',
                    "حقوق صورة تنتهي في {$date}", "Image rights end on {$date}",
                    dedupeKey: $key, subject: $media,
                    recommendedAction: 'renew-or-replace',
                );
                $raised++;
            } else {
                $this->signals->resolve($key);
            }
        });

        return $raised;
    }

    /** The screen that fixes the issue, when there is one. */
    private function href(Signal $signal): ?string
    {
        $subject = $signal->subject;
        $route = null;
        $params = [];
        if ($subject instanceof Media) {
            [$route, $params] = ['dashboard.media.edit', ['media' => $subject->id]];
        } elseif ($subject instanceof Fact) {
            $key = $subject->key;
            if (preg_match('/^(branch|hours)\.([A-Z0-9-]+)\./', $key, $m) === 1) {
                $branch = Branch::query()->where('code', $m[2])->first();
                [$route, $params] = $branch !== null ? ['dashboard.branches.show', ['branch' => $branch->id]] : ['dashboard.branches.index', []];
            } elseif (str_starts_with($key, 'contact.')) {
                $route = 'dashboard.contacts.index';
            } elseif (str_starts_with($key, 'city.')) {
                $route = 'dashboard.branches.index';
            } else {
                $route = 'dashboard.settings';
            }
        }

        return $route !== null && Route::has($route) ? route($route, $params) : null;
    }
}
