<?php

namespace App\Services\Content;

use App\Models\TeamMember;
use App\Services\MasterData\MasterData;
use App\Services\Media\MediaImage;
use App\Services\Media\MediaLibrary;
use App\Services\Media\MediaRights;
use Illuminate\Support\Carbon;

/**
 * SHELTER Family profiles a visitor may see (SI-B15, DX-002…006): published, not archived, the employee's consent on
 * record and not withdrawn, name and title in both languages. The photo appears only when the asset itself may be used
 * on the website — approved, and the person in it consented (MediaRights); otherwise the profile shows its initial.
 * Join date and bio appear only when switched on. The branch shows by its approved name. No HR data exists here.
 */
final class Team
{
    public function __construct(private readonly MediaLibrary $media, private readonly MasterData $data) {}

    /** @return list<array{member: TeamMember, name: string, title: string, branch: ?string, joined: ?string, bio: ?string, image: ?MediaImage, initial: string}> */
    public function published(string $locale): array
    {
        return $this->cache[$locale] ??= $this->load($locale);
    }

    /** @var array<string, list<array{member: TeamMember, name: string, title: string, branch: ?string, joined: ?string, bio: ?string, image: ?MediaImage, initial: string}>> */
    private array $cache = [];

    /** @return list<array{member: TeamMember, name: string, title: string, branch: ?string, joined: ?string, bio: ?string, image: ?MediaImage, initial: string}> */
    private function load(string $locale): array
    {
        $members = TeamMember::query()->with(['photo', 'branch'])->where('is_published', true)->whereNull('archived_at')
            ->whereNotNull('publish_consent_at')->whereNotNull('publish_consent_version')->whereNull('consent_withdrawn_at')
            ->orderBy('sort_order')->orderBy('id')->get();
        $ar = $locale === 'ar';
        $list = [];
        foreach ($members as $member) {
            if (blank($member->display_name_ar) || blank($member->display_name_en) || blank($member->job_title_ar) || blank($member->job_title_en)) {
                continue;
            }
            $name = (string) ($ar ? $member->display_name_ar : $member->display_name_en);
            $bio = $ar ? $member->bio_ar : $member->bio_en;
            $branch = $member->branch !== null ? $this->data->branchField($member->branch, $ar ? 'name_ar' : 'name_en') : null;
            $list[] = [
                'member' => $member,
                'name' => $name,
                'title' => (string) ($ar ? $member->job_title_ar : $member->job_title_en),
                'branch' => is_string($branch) ? $branch : null,
                'joined' => $member->show_join_date ? $this->monthYear($member->join_date, $ar) : null,
                'bio' => $member->show_bio && filled($bio) ? (string) $bio : null,
                'image' => MediaRights::canUse($member->photo) ? $this->media->image($member->photo, $locale, $name) : null,
                'initial' => mb_substr($name, 0, 1),
            ];
        }

        return $list;
    }

    /** "May 2023" / "أيار 2023" — Levantine month names on Arabic pages, Latin digits (D-065). */
    private function monthYear(mixed $date, bool $ar): ?string
    {
        $date = Carbon::make($date);
        if ($date === null) {
            return null;
        }
        $localized = $date->locale($ar ? 'ar_JO' : 'en');

        return ($localized instanceof Carbon ? $localized : $date)->isoFormat('MMMM YYYY');
    }
}
