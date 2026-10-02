<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Media\MediaLibrary;
use App\Services\Media\MediaRights;
use Illuminate\Console\Command;

/**
 * Records the Owner's approval of one asset (with the decision that approved it) and makes its web copies. Only for an
 * approval the Owner actually gave: this is the console stand-in for the Media Center's "Approve" until the Owner
 * Dashboard ships. The asset still has to pass MediaRights (people consent, rights expiry, source) to appear anywhere.
 */
final class MediaApprove extends Command
{
    protected $signature = 'media:approve {code : MED-00001}
        {--ref= : The Owner decision that approved it (required)}
        {--website : Allowed on the website}
        {--ads : Allowed in ads}
        {--expires= : Rights end date (YYYY-MM-DD)}';

    protected $description = 'Record the Owner approval of a media asset and generate its web copies';

    public function handle(MediaLibrary $library, AuditLogger $audit): int
    {
        $media = Media::query()->where('code', (string) $this->argument('code'))->first();
        $ref = trim((string) $this->option('ref'));
        if ($media === null || $ref === '') {
            $this->error($media === null ? 'Unknown asset.' : 'The approving decision (--ref) is required.');

            return self::FAILURE;
        }
        $before = $media->only(['approval_status', 'ok_website', 'ok_ads', 'rights_expires_at']);
        $media->forceFill([
            'approval_status' => Media::APPROVED,
            'approved_at' => now(),
            'approved_by' => User::query()->orderBy('id')->value('id'),
            'approval_ref' => $ref,
            'ok_website' => (bool) $this->option('website'),
            'ok_ads' => (bool) $this->option('ads'),
            'rights_expires_at' => $this->option('expires') ?: null,
        ])->save();
        $audit->record('media.approved', $media, ['before' => $before, 'after' => $media->only(array_keys($before)) + ['ref' => $ref]]);

        if (! MediaRights::canUse($media, 'website')) {
            $this->warn("{$media->code} approved, but not usable on the website yet (channel, people consent, expiry or source).");

            return self::SUCCESS;
        }
        $library->generateVariants($media);
        $this->info("{$media->code} approved ({$ref}) · web copies: ".implode(', ', array_keys($media->variants ?? [])));

        return self::SUCCESS;
    }
}
