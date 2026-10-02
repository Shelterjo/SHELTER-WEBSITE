<?php

namespace App\Services\Recruitment;

/**
 * Technical upload limits (CLOUDWAYS-RECRUITMENT-ARCHITECTURE §4): never a business rule. Per file: the server's own
 * limit (min of upload_max_filesize and post_max_size) minus a 10% margin, unless the Cloudways audit (A-07) sets an
 * explicit number. Count and total per application are flood guards, shown only when reached.
 */
final class UploadLimits
{
    public static function maxFileBytes(): int
    {
        $explicit = config('careers.uploads.max_file_bytes');
        if (is_int($explicit) && $explicit > 0) {
            return $explicit;
        }
        $server = min(self::iniBytes((string) ini_get('upload_max_filesize')), self::iniBytes((string) ini_get('post_max_size')));

        return (int) floor($server * 0.9);
    }

    public static function maxFiles(): int
    {
        return (int) config('careers.uploads.max_files');
    }

    public static function maxTotalBytes(): int
    {
        return (int) config('careers.uploads.max_total_bytes');
    }

    public static function iniBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '0' || $value === '-1') {
            return PHP_INT_MAX;
        }
        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
