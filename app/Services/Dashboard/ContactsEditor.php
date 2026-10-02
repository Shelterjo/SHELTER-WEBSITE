<?php

namespace App\Services\Dashboard;

use App\Enums\ContactKind;
use App\Models\ContactPoint;
use App\Models\SocialLink;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Recruitment\ApplicantInput;
use Illuminate\Support\Facades\DB;

/**
 * The Owner's contact numbers and social accounts (no code — M50, CMS-030, MDH-008, CONTACT-004…027). One number per
 * purpose (D-057): the Owner changes the value and whether it is public; WHERE each kind may appear stays fixed by the
 * approved rules (complaints and catering never on branch cards, header or footer). Saving is the Owner's approval of
 * the value (FACT-REGISTRY §3) — with a reason, audited. A social account appears on the site (footer, Schema sameAs)
 * only once the Owner adds and switches it on here (CONTACT-026/027).
 */
final class ContactsEditor
{
    /** Social platforms and the hosts their links must point to. */
    public const PLATFORMS = [
        'instagram' => ['instagram.com'],
        'facebook' => ['facebook.com', 'fb.com'],
        'tiktok' => ['tiktok.com'],
        'snapchat' => ['snapchat.com'],
        'youtube' => ['youtube.com', 'youtu.be'],
        'x' => ['x.com', 'twitter.com'],
        'linkedin' => ['linkedin.com'],
    ];

    private const REASON_MAX = 300;

    public function __construct(private readonly OwnerApproval $approval, private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string> errors; empty = saved
     */
    public function saveContact(ContactPoint $point, array $input, User $owner): array
    {
        $errors = [];
        $raw = is_string($input['value'] ?? null) ? trim($input['value']) : '';
        $value = null;
        if ($point->kind === ContactKind::Email) {
            $value = filter_var($raw, FILTER_VALIDATE_EMAIL) !== false ? mb_strtolower($raw) : null;
            if ($value === null) {
                $errors['value'] = (string) __('dashboard.contacts.errors.email');
            }
        } else {
            $value = ApplicantInput::phone($raw);
            if ($value === null || ! str_starts_with($value, '+')) {
                $errors['value'] = (string) __('dashboard.contacts.errors.phone');
                $value = null;
            }
        }
        $reason = is_string($input['reason'] ?? null) ? trim($input['reason']) : '';
        if ($reason === '' || mb_strlen($reason) > self::REASON_MAX) {
            $errors['reason'] = (string) __('dashboard.hours.errors.reason');
        }
        if ($errors !== [] || $value === null) {
            return $errors;
        }

        DB::transaction(function () use ($point, $value, $input, $reason, $owner): void {
            $before = $point->only(['value', 'is_public']);
            $point->forceFill(['value' => $value, 'is_public' => ! empty($input['is_public'])])->save();
            $this->approval->approve($point->factKey(), $value, $owner, 'contacts', $point->label_ar, $point->label_en);
            $this->audit->record('contacts.saved', $point, ['before' => $before, 'after' => $point->only(['value', 'is_public'])], ['reason' => $reason], actor: $owner);
        });

        return [];
    }

    /**
     * Adds or changes the account of one platform; switching it on is the Owner's approval of that account.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string> errors; empty = saved
     */
    public function saveSocial(string $platform, array $input, User $owner): array
    {
        if (! array_key_exists($platform, self::PLATFORMS)) {
            return ['platform' => (string) __('dashboard.contacts.errors.platform')];
        }
        $url = is_string($input['url'] ?? null) ? trim($input['url']) : '';
        $active = ! empty($input['is_active']);
        $errors = [];
        if ($url !== '' && ! self::validUrl($platform, $url)) {
            $errors['url'] = (string) __('dashboard.contacts.errors.url', ['platform' => __('dashboard.contacts.platforms.'.$platform)]);
        }
        if ($active && $url === '') {
            $errors['url'] = (string) __('dashboard.contacts.errors.url_required');
        }
        if ($errors !== []) {
            return $errors;
        }

        DB::transaction(function () use ($platform, $url, $active, $owner): void {
            $link = SocialLink::query()->firstOrNew(['platform' => $platform]);
            $before = $link->exists ? $link->only(['url', 'is_active']) : [];
            $link->forceFill(['url' => $url === '' ? null : $url, 'is_active' => $active, 'sort' => array_search($platform, array_keys(self::PLATFORMS), true)])->save();
            if ($active && $url !== '') {
                $this->approval->approve('social.'.$platform, $url, $owner, 'social', 'حساب '.$platform, $platform.' account');
            }
            $this->audit->record('social.saved', $link, ['before' => $before, 'after' => $link->only(['url', 'is_active'])], actor: $owner);
        });

        return [];
    }

    private static function validUrl(string $platform, string $url): bool
    {
        if (! str_starts_with($url, 'https://') || filter_var($url, FILTER_VALIDATE_URL) === false || mb_strlen($url) > 255) {
            return false;
        }
        $host = mb_strtolower((string) parse_url($url, PHP_URL_HOST));
        foreach (self::PLATFORMS[$platform] as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                return true;
            }
        }

        return false;
    }
}
