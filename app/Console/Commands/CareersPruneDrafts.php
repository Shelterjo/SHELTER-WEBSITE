<?php

namespace App\Console\Commands;

use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\UploadSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Removes expired DRAFT uploads — files of forms that were never submitted (RECRUITMENT-SECURITY §2.5). They are not
 * applications: submitted applications and their files are never deleted automatically (M28 §52).
 */
final class CareersPruneDrafts extends Command
{
    protected $signature = 'careers:prune-drafts';

    protected $description = 'Remove unsubmitted careers uploads older than the draft lifetime';

    public function handle(): int
    {
        $sessions = 0;
        $files = 0;
        UploadSession::query()->where('expires_at', '<', now())->each(function (UploadSession $session) use (&$sessions, &$files): void {
            foreach ($session->attachments()->whereNull('application_id')->get() as $draft) {
                /** @var ApplicationAttachment $draft */
                Storage::disk('careers')->delete($draft->storage_path);
                $draft->delete();
                $files++;
            }
            if (! $session->attachments()->exists()) {
                $session->delete();
                $sessions++;
            }
        });
        $this->info("careers:prune-drafts: {$files} draft files, {$sessions} sessions");

        return self::SUCCESS;
    }
}
