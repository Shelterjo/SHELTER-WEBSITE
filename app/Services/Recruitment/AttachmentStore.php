<?php

namespace App\Services\Recruitment;

use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\UploadSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Progressive upload of one file into a form's draft session (RECRUITMENT-SECURITY §2): size against the technical
 * limit, flood guards, clean display name, signature-based type check, sha256, then a random 128-bit storage key in the
 * private quarantine `tmp/`. On submit the files move to `YYYY/MM/` and join the application.
 */
final class AttachmentStore
{
    public function __construct(private readonly FileInspector $inspector, private readonly CvDetector $cv) {}

    /** @return ApplicationAttachment|string the stored draft, or an error code (too_large, too_many, total, + FileInspection reasons) */
    public function store(UploadSession $session, UploadedFile $file): ApplicationAttachment|string
    {
        if (! $file->isValid() || (int) $file->getSize() > UploadLimits::maxFileBytes()) {
            return 'too_large';
        }
        $existing = $session->attachments()->get();
        if ($existing->count() >= UploadLimits::maxFiles()) {
            return 'too_many';
        }
        if ((int) $existing->sum('size_bytes') + (int) $file->getSize() > UploadLimits::maxTotalBytes()) {
            return 'total';
        }

        $original = FileInspector::cleanName($file->getClientOriginalName());
        $path = (string) $file->getRealPath();
        $inspection = $this->inspector->inspect($path, $original);
        if (! $inspection->accepted) {
            return (string) $inspection->reason;
        }

        $key = bin2hex(random_bytes(16));
        $storagePath = 'tmp/'.$key;
        Storage::disk('careers')->putFileAs('tmp', $file, $key);

        return ApplicationAttachment::query()->create([
            'upload_session_id' => $session->id,
            'storage_key' => $key,
            'storage_path' => $storagePath,
            'original_filename' => $original,
            'extension' => FileInspector::extension($original),
            'declared_mime' => mb_substr((string) $file->getClientMimeType(), 0, 127),
            'detected_mime' => (string) $inspection->mime,
            'file_family' => (string) $inspection->family,
            'size_bytes' => (int) $file->getSize(),
            'sha256' => (string) hash_file('sha256', $path),
            'cv_score' => $this->cv->score($original, $path, (string) $inspection->family),
            'cv_detection' => 'pending',
            'scan_status' => 'scan_unavailable', // no local antivirus yet (A-11): strict type policy + download-only
        ]);
    }

    public function remove(ApplicationAttachment $attachment): void
    {
        Storage::disk('careers')->delete($attachment->storage_path);
        $attachment->delete();
    }
}
