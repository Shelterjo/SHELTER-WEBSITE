<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

/**
 * The media library's file side (MEDIA-RIGHTS, MEDIA-006/008/011/012): bringing a file in (checked by its content,
 * never stored twice — sha256), and making the web copies of an APPROVED image only (resized WebP, plus AVIF where the
 * server supports it; content-hashed names, so a replaced image never shows a stale copy). Originals stay private.
 */
final class MediaLibrary
{
    /** Web widths (px) — never upscaled beyond the original. */
    public const WIDTHS = [480, 800, 1200, 1600];

    private const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    /**
     * A new asset is always PENDING OWNER APPROVAL with no channel allowed (MEDIA-003).
     *
     * @param  array<string, mixed>  $rights  photographer, source, rights_holder, license, license_note, alt_ar, alt_en, people_consent, restrictions
     */
    public function import(string $path, array $rights): Media
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException("No readable file at [{$path}].");
        }
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (! isset(self::IMAGE_TYPES[$mime])) {
            throw new InvalidArgumentException("Unsupported file type [{$mime}] — JPEG, PNG or WebP images only.");
        }
        $source = (string) ($rights['source'] ?? '');
        if (! in_array($source, Media::SOURCES, true)) {
            throw new InvalidArgumentException('A source is required: '.implode(' · ', Media::SOURCES).'.');
        }
        $sha = (string) hash_file('sha256', $path);
        $existing = Media::query()->where('sha256', $sha)->first();
        if ($existing !== null) {
            return $existing; // MR-T08: the same file is the same asset
        }
        $size = getimagesize($path);
        if ($size === false) {
            throw new InvalidArgumentException('The file is not a readable image.');
        }

        return DB::transaction(function () use ($path, $rights, $mime, $sha, $size, $source): Media {
            $stored = substr($sha, 0, 2).'/'.$sha.'.'.self::IMAGE_TYPES[$mime];
            Storage::disk('media')->put($stored, (string) file_get_contents($path));
            $next = ((int) Media::query()->lockForUpdate()->max('id')) + 1;

            // Reloaded so the defaults the table sets (PENDING OWNER APPROVAL, no channel) are on the model too.
            return Media::query()->create([
                'code' => sprintf('MED-%05d', $next),
                'kind' => 'image',
                'original_path' => $stored,
                'mime' => $mime,
                'width' => $size[0],
                'height' => $size[1],
                'bytes' => (int) filesize($path),
                'sha256' => $sha,
                'alt_ar' => $rights['alt_ar'] ?? null,
                'alt_en' => $rights['alt_en'] ?? null,
                'photographer' => $rights['photographer'] ?? null,
                'source' => $source,
                'rights_holder' => $rights['rights_holder'] ?? null,
                'license' => $rights['license'] ?? 'full',
                'license_note' => $rights['license_note'] ?? null,
                'people_consent' => $rights['people_consent'] ?? 'not_recorded',
                'restrictions' => $rights['restrictions'] ?? null,
            ])->refresh();
        });
    }

    /**
     * Web copies for an approved, website-usable asset only. Returns false (and makes nothing) otherwise.
     */
    public function generateVariants(Media $media): bool
    {
        if (! MediaRights::canUse($media, 'website')) {
            return false;
        }
        $original = Storage::disk('media')->path($media->original_path);
        $image = match ($media->mime) {
            'image/jpeg' => imagecreatefromjpeg($original),
            'image/png' => imagecreatefrompng($original),
            'image/webp' => imagecreatefromwebp($original),
            default => false,
        };
        if ($image === false) {
            throw new RuntimeException("Cannot read the original of {$media->code}.");
        }
        $width = imagesx($image);
        $height = imagesy($image);
        $formats = ['image/webp' => 'webp'];
        if (function_exists('imageavif') && (gd_info()['AVIF Support'] ?? false)) {
            $formats = ['image/avif' => 'avif'] + $formats;
        }
        $disk = Storage::disk('media_public');
        $variants = [];
        $targets = array_values(array_unique(array_filter([...self::WIDTHS, $width], fn (int $w): bool => $w <= $width)));
        sort($targets);
        foreach ($targets as $target) {
            $copy = $target === $width ? $image : imagescale($image, $target, (int) round($height * $target / $width), IMG_BICUBIC);
            if ($copy === false) {
                continue;
            }
            foreach ($formats as $type => $extension) {
                $name = substr($media->sha256, 0, 2).'/'.substr($media->sha256, 0, 16).'-'.$target.'.'.$extension;
                ob_start();
                $ok = $extension === 'avif' ? imageavif($copy, null, 55, 6) : imagewebp($copy, null, 80);
                $bytes = (string) ob_get_clean();
                if ($ok && $bytes !== '') {
                    $disk->put($name, $bytes);
                    $variants[$type][] = ['width' => $target, 'path' => $name];
                }
            }
        }
        $media->forceFill(['variants' => $variants, 'variants_generated_at' => now()])->save();

        return $variants !== [];
    }

    /**
     * The approved image for a page, or null — the caller then shows nothing (no empty frame — DX-012). An image needs
     * its alternative text in the page language (MEDIA-006) unless the caller names one (a person's own name).
     */
    public function image(?Media $media, string $locale, ?string $fallbackAlt = null): ?MediaImage
    {
        if ($media === null || ! MediaRights::canUse($media, 'website') || empty($media->variants) || $media->width === null || $media->height === null) {
            return null;
        }
        $alt = $media->alt($locale) ?? $fallbackAlt;
        if ($alt === null || trim($alt) === '') {
            return null;
        }
        $disk = Storage::disk('media_public');
        $sources = [];
        foreach ($media->variants as $type => $copies) {
            $sources[$type] = implode(', ', array_map(fn (array $c): string => $disk->url($c['path']).' '.$c['width'].'w', $copies));
        }
        $fallback = $media->variants['image/webp'] ?? reset($media->variants);
        $largest = is_array($fallback) ? end($fallback) : false;
        if ($largest === false) {
            return null;
        }

        return new MediaImage(
            $disk->url($largest['path']), $sources, $media->width, $media->height,
            $alt, $media->focal_x, $media->focal_y,
        );
    }
}
