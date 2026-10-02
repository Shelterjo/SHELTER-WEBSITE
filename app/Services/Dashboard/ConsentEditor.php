<?php

namespace App\Services\Dashboard;

use App\Models\Recruitment\ConsentVersion;
use App\Models\User;
use App\Services\Core\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * The consent and acknowledgement texts the public forms ask people to accept (M50, CAREERS §21, PF-02/PF-03). A text
 * is never edited in place: a change is a NEW version that becomes the active one, and every application keeps the
 * exact version it accepted. Legal wording — the Owner's (ideally reviewed by his lawyer, PO-019). Careers is Arabic
 * only; the partnership texts are bilingual.
 */
final class ConsentEditor
{
    /** Scope → its languages. */
    public const SCOPES = ['careers' => ['ar'], 'partnerships' => ['ar', 'en'], 'partnership_ack' => ['ar', 'en']];

    public const MAX = 3000;

    public function __construct(private readonly AuditLogger $audit) {}

    public static function active(string $scope): ?ConsentVersion
    {
        return ConsentVersion::query()->where('scope', $scope)->where('is_active', true)->orderByDesc('active_from')->orderByDesc('id')->first();
    }

    /**
     * @param  array<string, mixed>  $input  text_ar, text_en, reason
     * @return array{version: ConsentVersion|null, errors: array<string, string>}
     */
    public function publish(string $scope, array $input, User $owner): array
    {
        if (! array_key_exists($scope, self::SCOPES)) {
            return ['version' => null, 'errors' => ['scope' => 'unknown']];
        }
        $languages = self::SCOPES[$scope];
        $errors = [];
        $texts = ['ar' => ''];
        foreach ($languages as $locale) {
            $raw = is_string($input['text_'.$locale] ?? null) ? $input['text_'.$locale] : '';
            $text = trim((string) preg_replace(['/\r\n?/', '/[ \t]+/u', '/\n{3,}/'], ["\n", ' ', "\n\n"], $raw));
            if ($text === '') {
                $errors['text_'.$locale] = (string) __('dashboard.consents.errors.required');
            } elseif (mb_strlen($text) > self::MAX) {
                $errors['text_'.$locale] = (string) __('dashboard.pages.errors.too_long', ['max' => self::MAX]);
            }
            $texts[$locale] = $text;
        }
        $reason = is_string($input['reason'] ?? null) ? trim($input['reason']) : '';
        if ($reason === '' || mb_strlen($reason) > 300) {
            $errors['reason'] = (string) __('dashboard.hours.errors.reason');
        }
        $current = self::active($scope);
        if ($errors === [] && $current !== null && $current->text_ar === $texts['ar'] && ($texts['en'] ?? null) === ($languages === ['ar'] ? null : $current->text_en)) {
            $errors['text_ar'] = (string) __('dashboard.consents.errors.same');
        }
        if ($errors !== []) {
            return ['version' => null, 'errors' => $errors];
        }

        $version = DB::transaction(function () use ($scope, $texts, $reason, $owner, $current): ConsentVersion {
            $n = ConsentVersion::query()->where('scope', $scope)->lockForUpdate()->count() + 1;
            do {
                $name = sprintf('%s-v%d', $scope === 'careers' ? 'careers-consent' : ($scope === 'partnerships' ? 'partnership-consent' : 'partnership-ack'), $n++);
            } while (ConsentVersion::query()->where('version', $name)->exists());
            ConsentVersion::query()->where('scope', $scope)->where('is_active', true)->update(['is_active' => false]);
            $version = ConsentVersion::query()->create([
                'scope' => $scope, 'version' => $name, 'text_ar' => $texts['ar'], 'text_en' => $texts['en'] ?? null,
                'active_from' => now(), 'is_active' => true,
            ]);
            $this->audit->record('consent.published', $version, ['before' => ['version' => $current?->version], 'after' => ['version' => $name]], ['scope' => $scope, 'reason' => $reason], actor: $owner);

            return $version;
        });

        return ['version' => $version, 'errors' => []];
    }
}
