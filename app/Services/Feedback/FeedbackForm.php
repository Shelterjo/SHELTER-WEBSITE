<?php

namespace App\Services\Feedback;

use App\Models\Feedback;
use App\Services\Recruitment\ApplicantInput;
use App\Services\Site\BranchDirectory;
use App\Services\Site\BranchSummary;
use App\Services\Site\Markets;

/**
 * The Voice of Customer form (VOICE-OF-CUSTOMER §2): which branches it offers, whether it is open, validation and
 * saving. Branch and overall experience are required; the four other ratings and the comment are optional. Nothing
 * personal is collected or stored — not even the address the answer came from.
 */
final class FeedbackForm
{
    public function __construct(private readonly Markets $markets, private readonly BranchDirectory $directory) {}

    /** @return list<BranchSummary> the market's public branches (approved names only) */
    public function branches(string $locale): array
    {
        $market = $this->markets->current();

        return $market !== null ? $this->directory->forMarket($market, $locale) : [];
    }

    /** Production: closed until the Owner decides the questions and entry points and the release gates pass. */
    public function isOpen(string $locale): bool
    {
        return $this->branches($locale) !== []
            && (! app()->isProduction() || config('feedback.form_enabled_in_production') === true);
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  list<BranchSummary>  $branches
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    public function validate(array $input, array $branches): array
    {
        $errors = [];
        $chosen = null;
        foreach ($branches as $summary) {
            if (($input['branch'] ?? null) === $summary->branch->slug) {
                $chosen = $summary->branch->id;
            }
        }
        if ($chosen === null) {
            $errors['branch'] = (string) __(($input['branch'] ?? '') === '' ? 'feedback.errors.required' : 'feedback.errors.choose');
        }

        $data = ['branch_id' => $chosen];
        foreach (Feedback::DIMENSIONS as $dimension) {
            $raw = $input['rating_'.$dimension] ?? null;
            $value = is_string($raw) && preg_match('/^[1-5]$/', $raw) === 1 ? (int) $raw : null;
            $data['rating_'.$dimension] = $value;
            if ($value === null && ($dimension === 'overall' || ($raw !== null && $raw !== ''))) {
                $errors['rating_'.$dimension] = (string) __($raw === null || $raw === '' ? 'feedback.errors.required' : 'feedback.errors.choose');
            }
        }

        $comment = ApplicantInput::text(is_string($input['comment'] ?? null) ? $input['comment'] : '', multiline: true);
        $max = (int) config('feedback.limits.comment');
        if (mb_strlen($comment) > $max) {
            $errors['comment'] = (string) __('feedback.errors.too_long', ['max' => $max]);
        }
        $data['comment'] = $comment !== '' ? $comment : null;

        return ['data' => $data, 'errors' => $errors];
    }

    /**
     * Saved first, confirmed after (VOICE-OF-CUSTOMER §2 reliability). The same idempotency key always returns the same row.
     *
     * @param  array<string, mixed>  $data  validate() output
     */
    public function record(array $data, string $locale, string $entryPoint, string $idempotencyKey): Feedback
    {
        $existing = Feedback::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($existing !== null) {
            return $existing;
        }

        return Feedback::query()->create([
            'branch_id' => $data['branch_id'],
            'rating_overall' => $data['rating_overall'],
            'rating_coffee' => $data['rating_coffee'],
            'rating_service' => $data['rating_service'],
            'rating_cleanliness' => $data['rating_cleanliness'],
            'rating_speed' => $data['rating_speed'],
            'comment' => $data['comment'],
            'locale' => $locale,
            'entry_point' => $entryPoint,
            'idempotency_key' => $idempotencyKey,
            'form_version' => (string) config('feedback.form_version'),
            'submitted_at' => now(),
        ]);
    }
}
