<?php

namespace App\Services\Experiences;

use App\Models\Experience;
use App\Models\Media;
use App\Services\Content\Team;
use App\Services\Core\FeatureFlags;
use App\Services\Media\MediaImage;
use App\Services\Media\MediaLibrary;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

/**
 * Employee of the Month (DX-007…009, DYNAMIC-EXPERIENCE-ENGINE §2: an experience of type `recognition` that points to
 * a SHELTER Family profile — `details.team_member_id` — for one month, `details.month`, Amman time). What a visitor may
 * see on a place (home · SHELTER Family · Media Center) now: published, inside its month, not archived or stopped, the
 * title in both languages and the text in both or neither — and ONLY while the person is on SHELTER Family with their
 * recorded consent (Team::published) and the photo may be used on the website (MediaRights through
 * MediaLibrary::image; the recognition's own photo, or else the profile photo). Anything missing → null → nothing is
 * drawn (DX-012). Safe Mode stops it like the rest of the dynamic layer. A failure never breaks the page (DX-037).
 */
final class Recognitions
{
    public const TYPE = 'recognition';

    /** Where the Owner may show it (DX-009). */
    public const PLACEMENTS = ['home', 'family', 'media'];

    public function __construct(private readonly Team $team, private readonly MediaLibrary $media, private readonly FeatureFlags $flags) {}

    public function current(string $placement, string $locale, ?CarbonImmutable $now = null): ?ShownRecognition
    {
        try {
            if ($this->flags->enabled(FeatureFlags::SAFE_MODE)) {
                return null;
            }
            foreach ($this->live($now ?? CarbonImmutable::now()) as $item) {
                $shown = in_array($placement, $item->placements ?? [], true) ? $this->present($item, $locale) : null;
                if ($shown !== null) {
                    return $shown;
                }
            }

            return null;
        } catch (Throwable $e) {
            report($e);

            return null; // the base site never depends on the engine
        }
    }

    /**
     * Published and inside its month now, not archived or stopped — newest month first.
     *
     * @return Collection<int, Experience>
     */
    public function live(CarbonImmutable $now): Collection
    {
        return Experience::query()->with('media')->where('type', self::TYPE)->whereIn('status', ['scheduled', 'active'])
            ->whereNull('archived_at')->where('emergency_disabled', false)
            ->where(fn ($q) => $q->whereNull('manual_state')->orWhere('manual_state', '!=', 'off'))
            ->where('origin', '!=', 'ai')
            ->where('starts_at', '<=', $now->utc())->where('ends_at', '>', $now->utc())
            ->orderByDesc('starts_at')->orderByDesc('id')->get();
    }

    /**
     * Why it would not be shown even inside its month — the same checks as the site, for the Owner Dashboard (empty =
     * nothing missing): member (not on SHELTER Family with consent) · photo (none the website may use) · text · place.
     *
     * @return list<string>
     */
    public function problems(Experience $item): array
    {
        $problems = [];
        $person = $this->person($item, 'ar');
        if ($person === null) {
            $problems[] = 'member';
        }
        // The profile photo can only be judged once the person is on SHELTER Family; a photo of its own, always.
        if (($person !== null || $item->media_id !== null) && $this->photo($item, $person, 'ar') === null) {
            $problems[] = 'photo';
        }
        if ($item->text('title', 'ar') === null || $item->text('title', 'en') === null || ($item->text('body', 'ar') === null) !== ($item->text('body', 'en') === null)) {
            $problems[] = 'text';
        }
        if (array_intersect($item->placements ?? [], self::PLACEMENTS) === []) {
            $problems[] = 'placement';
        }

        return $problems;
    }

    public static function memberId(Experience $item): ?int
    {
        $id = ($item->details ?? [])['team_member_id'] ?? null;

        return is_int($id) ? $id : null;
    }

    /** "2026-10" → its month in Amman time, or null. */
    public static function month(?string $month): ?CarbonImmutable
    {
        if ($month === null || preg_match('/^(\d{4})-(\d{2})$/', $month, $m) !== 1 || (int) $m[2] < 1 || (int) $m[2] > 12) {
            return null;
        }

        return CarbonImmutable::create((int) $m[1], (int) $m[2], 1, 0, 0, 0, 'Asia/Amman');
    }

    /** "تشرين الأول 2026" / "October 2026" — Levantine month names on Arabic pages, Latin digits (D-065). */
    public static function period(?string $month, string $locale): ?string
    {
        $start = self::month($month);

        return $start === null ? null : self::monthName($start, $locale, 'MMMM YYYY');
    }

    /** A month in the given language: "MMMM" → "تشرين الأول" / "October". */
    public static function monthName(CarbonImmutable $date, string $locale, string $format = 'MMMM'): string
    {
        $localized = $date->locale($locale === 'ar' ? 'ar_JO' : 'en');

        return ($localized instanceof CarbonImmutable ? $localized : $date)->isoFormat($format);
    }

    private function present(Experience $item, string $locale): ?ShownRecognition
    {
        $person = $this->person($item, $locale);
        $title = $item->text('title', $locale);
        $period = self::period(is_string($item->details['month'] ?? null) ? $item->details['month'] : null, $locale);
        if ($person === null || $title === null || $period === null || $this->problems($item) !== []) {
            return null;
        }
        $image = $this->photo($item, $person, $locale);

        return $image === null ? null : new ShownRecognition(
            id: $item->id,
            title: $title,
            text: $item->text('body', $locale),
            name: $person['name'],
            jobTitle: $person['title'],
            image: $image,
            period: $period,
        );
    }

    /** @return array{name: string, title: string, photo: Media|null}|null the profile exactly as SHELTER Family shows it */
    private function person(Experience $item, string $locale): ?array
    {
        $id = self::memberId($item);
        foreach ($id === null ? [] : $this->team->published($locale) as $entry) {
            if ($entry['member']->id === $id) {
                return ['name' => $entry['name'], 'title' => $entry['title'], 'photo' => $entry['member']->photo];
            }
        }

        return null;
    }

    /** @param  array{name: string, title: string, photo: Media|null}|null  $person */
    private function photo(Experience $item, ?array $person, string $locale): ?MediaImage
    {
        return $this->media->image($item->media_id !== null ? $item->media : ($person['photo'] ?? null), $locale, $person['name'] ?? null);
    }
}
