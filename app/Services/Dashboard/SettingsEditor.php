<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Services\Content\Search\SearchLog;
use App\Services\Core\FeatureFlags;
use App\Services\Core\Settings;
use App\Services\Feedback\FeedbackForm;
use App\Services\Franchise\FranchiseForm;
use App\Services\MasterData\MasterData;
use App\Services\Recruitment\CareersForm;
use Carbon\CarbonImmutable;

/**
 * Settings (M50 — no code): the Owner's switches and brand facts. Each public form open or closed (the Owner can always
 * close one; on the live site opening also needs the release gate — M36), Safe Mode (SAFE-MODE §2: announcements and
 * offers stop, an urgent notice stays as text), anonymous search counting (PO-019: say it in the privacy page first),
 * and the founding year (a brand fact: saving it approves it). Every change is audited; only changes are written.
 */
final class SettingsEditor
{
    /** Form → the flag that closes it. */
    public const FORMS = [
        'careers' => FeatureFlags::CAREERS_CLOSED,
        'partnership' => FeatureFlags::PARTNERSHIP_CLOSED,
        'feedback' => FeatureFlags::FEEDBACK_CLOSED,
    ];

    public function __construct(
        private readonly FeatureFlags $flags,
        private readonly Settings $settings,
        private readonly OwnerApproval $approval,
        private readonly MasterData $data,
    ) {}

    /** @return array{forms: array<string, array{open: bool, live: bool}>, safe_mode: bool, search_log: bool, founded_year: ?int} */
    public function state(): array
    {
        $live = [
            'careers' => app(CareersForm::class)->isOpen(),
            'partnership' => app(FranchiseForm::class)->isOpen(),
            'feedback' => app(FeedbackForm::class)->isOpen(app()->getLocale()),
        ];
        $forms = [];
        foreach (self::FORMS as $form => $flag) {
            $forms[$form] = ['open' => ! $this->flags->enabled($flag), 'live' => $live[$form]];
        }
        $year = $this->data->setting('brand.founded_year');

        return [
            'forms' => $forms,
            'safe_mode' => $this->flags->enabled(FeatureFlags::SAFE_MODE),
            'search_log' => $this->flags->enabled(SearchLog::FLAG),
            'founded_year' => is_numeric($year) ? (int) $year : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{errors: array<string, string>, changed: int}
     */
    public function save(array $input, User $owner): array
    {
        $errors = [];
        $year = is_string($input['founded_year'] ?? null) ? trim($input['founded_year']) : '';
        $current = (int) CarbonImmutable::now('Asia/Amman')->format('Y');
        if ($year !== '' && (preg_match('/^\d{4}$/', $year) !== 1 || (int) $year < 1950 || (int) $year > $current)) {
            $errors['founded_year'] = (string) __('dashboard.settings.errors.year', ['max' => $current]);
        }
        if ($errors !== []) {
            return ['errors' => $errors, 'changed' => 0];
        }
        $reason = is_string($input['reason'] ?? null) && trim($input['reason']) !== '' ? mb_substr(trim($input['reason']), 0, 300) : 'Owner Dashboard → Settings';
        $wanted = [];
        foreach (self::FORMS as $form => $flag) {
            if (in_array($input['form_'.$form] ?? null, ['open', 'closed'], true)) {
                $wanted[$flag] = $input['form_'.$form] === 'closed';
            }
        }
        foreach (['safe_mode' => FeatureFlags::SAFE_MODE, 'search_log' => SearchLog::FLAG] as $field => $flag) {
            if (in_array($input[$field] ?? null, ['on', 'off'], true)) {
                $wanted[$flag] = $input[$field] === 'on';
            }
        }
        $changed = 0;
        foreach ($wanted as $flag => $enabled) {
            if ($this->flags->enabled($flag) !== $enabled) {
                $this->flags->set($flag, $enabled, $owner, $reason);
                $changed++;
            }
        }
        $now = $this->data->setting('brand.founded_year');
        if ($year !== '' && (string) $now !== $year) {
            $this->settings->set('brand.founded_year', (int) $year, $owner, $reason, 'PUBLIC');
            $this->approval->approve('brand.founded_year', (int) $year, $owner, 'brand');
            $changed++;
        }

        return ['errors' => [], 'changed' => $changed];
    }
}
