<?php

namespace App\Services\Requests;

use App\Models\Recruitment\Application;
use App\Models\Recruitment\ApplicationInterview;
use App\Models\Recruitment\ApplicationNote;
use App\Models\Recruitment\ApplicationStatusChange;
use App\Models\Recruitment\InterviewLocation;
use App\Models\Recruitment\JobApplication;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Recruitment\IdentityVault;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * The Owner's actions on a job or partnership application (Applications Core, CAREERS-046…070, FRAN-055…058):
 * the first view (a read state, not a status), status changes with their history, archive and restore, internal notes,
 * interviews, revealing the identity number and — careers only, from the archive — permanent deletion. Every action is
 * audited without personal data (reference number and field names only; never the identity number).
 */
final class ApplicationInbox
{
    /** Partnership stages (FRAN-055, docs/franchise/04 §2) — proposed; the Franchise Master may change them (PO-048). */
    public const FR_STATUSES = ['received', 'qualified', 'meeting', 'market_review', 'site_review', 'approved', 'contract', 'closed', 'archived'];

    private const NOTE_MAX = 3000;

    public function __construct(private readonly AuditLogger $audit, private readonly IdentityVault $vault) {}

    /** @return list<string> */
    public static function statuses(string $type): array
    {
        return $type === 'FR' ? self::FR_STATUSES : JobApplication::STATUSES;
    }

    /** Opening an application the first time marks it as seen — it does not change its status (CAREERS-054). */
    public function markViewed(Application $application, User $owner): void
    {
        if ($application->first_viewed_at !== null) {
            return;
        }
        $application->forceFill(['first_viewed_at' => now(), 'first_viewed_by' => $owner->id])->save();
        $this->audit->record('application.first_viewed', $application, [], ['reference' => $application->reference_number], actor: $owner);
    }

    /**
     * Any status → any status for the Owner, recorded with its date (DATA-MODEL §6). Archiving keeps the status it had;
     * leaving the archive clears it.
     */
    public function changeStatus(Application $application, string $status, ?string $note, User $owner): void
    {
        if (! in_array($status, self::statuses($application->type), true)) {
            throw new InvalidArgumentException("Unknown status [{$status}].");
        }
        $old = $application->status;
        if ($status === $old) {
            return;
        }
        $note = $this->cleanNote($note);
        DB::transaction(function () use ($application, $status, $old, $note, $owner): void {
            $values = ['status' => $status];
            if ($status === 'archived') {
                $values += ['archived_at' => now(), 'status_before_archive' => $old];
            } elseif ($old === 'archived') {
                $values += ['archived_at' => null, 'status_before_archive' => null];
            }
            $application->forceFill($values)->save();
            ApplicationStatusChange::query()->create([
                'application_id' => $application->id, 'old_status' => $old, 'new_status' => $status,
                'actor_id' => $owner->id, 'changed_at' => now(), 'internal_note' => $note,
            ]);
            $this->audit->record('application.status_changed', $application, ['before' => ['status' => $old], 'after' => ['status' => $status]],
                ['reference' => $application->reference_number, 'with_note' => $note !== null], actor: $owner);
        });
    }

    /** Back to the status it had before it was archived. */
    public function restore(Application $application, User $owner): void
    {
        if ($application->status === 'archived') {
            $this->changeStatus($application, $application->status_before_archive ?? 'received', null, $owner);
        }
    }

    public function addNote(Application $application, string $body, User $owner): ApplicationNote
    {
        $body = $this->cleanNote($body) ?? throw new InvalidArgumentException('Empty note.');
        $note = ApplicationNote::query()->create(['application_id' => $application->id, 'body' => $body, 'author_id' => $owner->id]);
        $this->audit->record('note.added', $note, [], ['reference' => $application->reference_number], actor: $owner);

        return $note;
    }

    /** Edits and removals keep what the note said before (CAREERS-067). */
    public function editNote(ApplicationNote $note, string $body, User $owner): void
    {
        $body = $this->cleanNote($body) ?? throw new InvalidArgumentException('Empty note.');
        $before = $note->body;
        $note->forceFill(['body' => $body])->save();
        $this->audit->record('note.edited', $note, ['before' => ['body' => $before], 'after' => ['body' => $body]], actor: $owner);
    }

    public function deleteNote(ApplicationNote $note, User $owner): void
    {
        $note->delete();
        $this->audit->record('note.deleted', $note, ['before' => ['body' => $note->body]], actor: $owner);
    }

    /** The full identity number, for the Owner only — logged without the number itself (SECURITY §5). */
    public function revealIdentity(Application $application, User $owner): ?string
    {
        $identity = $application->identity;
        if ($identity === null) {
            return null;
        }
        $number = $this->vault->decrypt($identity->id_ciphertext, $identity->id_nonce, $identity->id_key_version);
        $this->audit->record('identity.revealed', $application, [], ['reference' => $application->reference_number], actor: $owner);

        return $number;
    }

    /** A new appointment replaces the current one; earlier ones stay as history (CAREERS-048). */
    public function scheduleInterview(Application $application, CarbonImmutable $at, InterviewLocation $location, ?string $notes, User $owner): ApplicationInterview
    {
        return DB::transaction(function () use ($application, $at, $location, $notes, $owner): ApplicationInterview {
            $changed = ApplicationInterview::query()->where('application_id', $application->id)->where('is_current', true)->update(['is_current' => false]) > 0;
            $interview = ApplicationInterview::query()->create([
                'application_id' => $application->id, 'interview_date' => $at->toDateString(), 'interview_time' => $at->format('H:i'),
                'location_id' => $location->id, 'internal_notes' => $this->cleanNote($notes), 'is_current' => true, 'created_by' => $owner->id,
            ]);
            $this->audit->record($changed ? 'interview.changed' : 'interview.created', $interview, [], ['reference' => $application->reference_number], actor: $owner);

            return $interview;
        });
    }

    /**
     * Careers only, from the archive only, one at a time (CAREERS-070, DATA-MODEL §7): the application, its files and
     * everything attached to it are removed in one go; a tombstone without personal data stays in the audit.
     */
    public function permanentlyDelete(Application $application, User $owner): void
    {
        if ($application->type !== 'JOB' || $application->status !== 'archived') {
            throw new InvalidArgumentException('Only an archived job application can be deleted permanently.');
        }
        $paths = $application->attachments()->pluck('storage_path')->all();
        $tombstone = ['reference' => $application->reference_number, 'submitted_at' => $application->submitted_at->toIso8601String(), 'attachments' => count($paths)];
        DB::transaction(function () use ($application): void {
            $application->delete(); // cascades: job, identity, attachments rows, consents, notes, history, interviews, links
        });
        Storage::disk('careers')->delete($paths);
        $this->audit->record('application.permanently_deleted', null, [], $tombstone, actor: $owner);
    }

    private function cleanNote(?string $note): ?string
    {
        $note = is_string($note) ? trim(str_replace(["\r\n", "\r"], "\n", $note)) : '';
        if ($note === '') {
            return null;
        }

        return mb_substr($note, 0, self::NOTE_MAX);
    }
}
