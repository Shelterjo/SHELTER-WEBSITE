<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\Media\MediaLibrary;
use Illuminate\Console\Command;
use InvalidArgumentException;

/**
 * Brings an image the Owner sent into the media library (until the Owner Dashboard's Media Center exists). It arrives
 * PENDING OWNER APPROVAL with no channel allowed (MEDIA-003) — importing never publishes. The same file twice is the
 * same asset (sha256).
 */
final class MediaImport extends Command
{
    protected $signature = 'media:import {file : Path to a JPEG, PNG or WebP image}
        {--source= : shelter · contracted · partner · other (stock · ai · google · legacy_site need explicit approval)}
        {--rights-holder= : Who owns the rights}
        {--photographer= : Who took it}
        {--license=full : full · website_only · time_limited · other}
        {--people=not_recorded : none (nobody in it) · recorded · not_recorded}
        {--alt-ar= : Alternative text (Arabic)}
        {--alt-en= : Alternative text (English)}';

    protected $description = 'Import an image into the media library as PENDING OWNER APPROVAL';

    public function handle(MediaLibrary $library): int
    {
        try {
            $media = $library->import((string) $this->argument('file'), [
                'source' => (string) $this->option('source'),
                'rights_holder' => $this->option('rights-holder'),
                'photographer' => $this->option('photographer'),
                'license' => (string) $this->option('license'),
                'people_consent' => in_array($this->option('people'), ['none', 'recorded', 'not_recorded'], true) ? $this->option('people') : 'not_recorded',
                'alt_ar' => $this->option('alt-ar'),
                'alt_en' => $this->option('alt-en'),
            ]);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        $this->info("{$media->code} · {$media->width}×{$media->height} · {$media->approval_status}");

        return self::SUCCESS;
    }
}
