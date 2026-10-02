<?php

namespace App\Services\Recruitment;

use ZipArchive;

/**
 * Upload type policy (RECRUITMENT-SECURITY §3): a file is accepted only when its ACTUAL signature belongs to a safe
 * family and agrees with its extension; everything else is refused, whatever its name. Executables, server scripts,
 * browser scripts (html, svg, xml), macro documents and archives are always refused. Office files are ZIP containers:
 * accepted only with a valid OOXML/ODF structure, no vbaProject.bin and bounded expansion (zip-bomb guard).
 */
final class FileInspector
{
    /** Extension → family, for the safe families only. */
    public const FAMILIES = [
        'pdf' => 'pdf',
        'doc' => 'word', 'docx' => 'word', 'odt' => 'word', 'rtf' => 'word',
        'xls' => 'spreadsheet', 'xlsx' => 'spreadsheet', 'ods' => 'spreadsheet', 'csv' => 'spreadsheet',
        'ppt' => 'presentation', 'pptx' => 'presentation', 'odp' => 'presentation',
        'txt' => 'text',
        'jpg' => 'image', 'jpeg' => 'image', 'png' => 'image', 'webp' => 'image', 'heic' => 'image', 'gif' => 'image',
        'tif' => 'image', 'tiff' => 'image', 'bmp' => 'image',
    ];

    /** Always refused, even as an inner extension ("cv.php.pdf"). */
    private const DANGEROUS = [
        'exe', 'dll', 'msi', 'com', 'scr', 'bat', 'cmd', 'ps1', 'vbs', 'jar', 'apk', 'app', 'dmg', 'sh', 'bin',
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'phps', 'py', 'pl', 'cgi', 'asp', 'aspx', 'jsp',
        'js', 'mjs', 'html', 'htm', 'svg', 'xml', 'xhtml', 'hta',
    ];

    private const MACRO = ['docm', 'xlsm', 'pptm', 'dotm', 'xltm', 'potm', 'ppsm'];

    private const ARCHIVE = ['zip', 'rar', '7z', 'tar', 'gz', 'tgz', 'bz2', 'xz', 'iso'];

    public static function extension(string $filename): string
    {
        $ext = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));

        return mb_substr($ext, 0, 16);
    }

    /** Original name kept as display metadata only: controls, path separators and "../" removed, 255 chars max. */
    public static function cleanName(string $filename): string
    {
        $name = (string) preg_replace('/[\x{0000}-\x{001F}\x{007F}\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', '', $filename);
        $name = str_replace(['../', '..\\', '/', '\\'], '', $name);
        $name = trim($name, " .\t");

        return mb_substr($name !== '' ? $name : 'file', 0, 255);
    }

    public function inspect(string $path, string $originalName): FileInspection
    {
        $size = @filesize($path);
        if ($size === false || $size === 0) {
            return FileInspection::reject('empty');
        }
        $parts = array_map('strtolower', array_slice(explode('.', self::cleanName($originalName)), 1));
        foreach ($parts as $part) {
            if (in_array($part, self::DANGEROUS, true)) {
                return FileInspection::reject('dangerous');
            }
        }
        $ext = self::extension($originalName);
        if (in_array($ext, self::MACRO, true)) {
            return FileInspection::reject('macro');
        }
        if (in_array($ext, self::ARCHIVE, true)) {
            return FileInspection::reject('archive');
        }

        $head = (string) file_get_contents($path, false, null, 0, 512);
        // Executable or script content is refused whatever the extension says (MZ, ELF, shebang, PHP/HTML/SVG markup).
        if (str_starts_with($head, 'MZ') || str_starts_with($head, "\x7FELF") || str_starts_with($head, '#!')
            || preg_match('/^\s*(<\?php|<\?=|<script|<!doctype html|<html|<svg|<\?xml)/i', $head) === 1) {
            return FileInspection::reject('dangerous');
        }

        $family = self::FAMILIES[$ext] ?? null;
        if ($family === null) {
            return FileInspection::reject('unsupported');
        }

        return match (true) {
            str_starts_with($head, '%PDF-') => $ext === 'pdf' ? $this->pdf($path) : FileInspection::reject('mismatch'),
            str_starts_with($head, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") => $this->ole($path, $ext),
            str_starts_with($head, "PK\x03\x04") => $this->container($path, $ext),
            str_starts_with($head, '{\\rtf') => $ext === 'rtf' ? FileInspection::accept('word', 'application/rtf') : FileInspection::reject('mismatch'),
            in_array($ext, ['txt', 'csv'], true) => $this->text($path, $ext),
            ($image = $this->imageMime($head)) !== null => $family === 'image' ? FileInspection::accept('image', $image) : FileInspection::reject('mismatch'),
            default => FileInspection::reject('mismatch'),
        };
    }

    private function pdf(string $path): FileInspection
    {
        // Active content (JavaScript, launch actions) is refused when it can be seen without executing anything.
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return FileInspection::reject('unsupported');
        }
        $tail = '';
        while (! feof($handle)) {
            $chunk = $tail.(string) fread($handle, 1 << 20);
            if (preg_match('#/(JavaScript|JS|Launch)\b#', $chunk) === 1) {
                fclose($handle);

                return FileInspection::reject('active_content');
            }
            $tail = substr($chunk, -16);
        }
        fclose($handle);

        return FileInspection::accept('pdf', 'application/pdf');
    }

    private function ole(string $path, string $ext): FileInspection
    {
        $types = ['doc' => ['word', 'application/msword'], 'xls' => ['spreadsheet', 'application/vnd.ms-excel'], 'ppt' => ['presentation', 'application/vnd.ms-powerpoint']];
        if (! isset($types[$ext])) {
            return FileInspection::reject('mismatch');
        }
        // A VBA project storage inside the compound file = a macro document.
        $content = (string) file_get_contents($path);
        if (str_contains($content, mb_convert_encoding('_VBA_PROJECT', 'UTF-16LE', 'UTF-8')) || str_contains($content, mb_convert_encoding('Macros', 'UTF-16LE', 'UTF-8'))) {
            return FileInspection::reject('macro');
        }

        return FileInspection::accept($types[$ext][0], $types[$ext][1]);
    }

    private function container(string $path, string $ext): FileInspection
    {
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            return FileInspection::reject('unsupported');
        }
        try {
            if ($zip->numFiles > (int) config('careers.uploads.max_zip_entries')) {
                return FileInspection::reject('archive');
            }
            $expanded = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if ($stat === false) {
                    return FileInspection::reject('unsupported');
                }
                $expanded += (int) $stat['size'];
                if (str_ends_with(strtolower($stat['name']), 'vbaproject.bin')) {
                    return FileInspection::reject('macro');
                }
            }
            if ($expanded > (int) config('careers.uploads.max_zip_expanded_bytes')) {
                return FileInspection::reject('archive');
            }

            $odf = $zip->getFromName('mimetype');
            if (is_string($odf)) {
                $odfTypes = ['odt' => 'application/vnd.oasis.opendocument.text', 'ods' => 'application/vnd.oasis.opendocument.spreadsheet', 'odp' => 'application/vnd.oasis.opendocument.presentation'];

                return isset($odfTypes[$ext]) && trim($odf) === $odfTypes[$ext]
                    ? FileInspection::accept(self::FAMILIES[$ext], $odfTypes[$ext])
                    : FileInspection::reject('mismatch');
            }

            $contentTypes = $zip->getFromName('[Content_Types].xml');
            if (! is_string($contentTypes)) {
                return FileInspection::reject('archive'); // a plain archive, not an office document
            }
            if (stripos($contentTypes, 'macroEnabled') !== false) {
                return FileInspection::reject('macro');
            }
            $ooxml = [
                'docx' => ['word/document.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'xlsx' => ['xl/workbook.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                'pptx' => ['ppt/presentation.xml', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            ];
            if (! isset($ooxml[$ext]) || $zip->locateName($ooxml[$ext][0]) === false) {
                return FileInspection::reject('mismatch');
            }

            return FileInspection::accept(self::FAMILIES[$ext], $ooxml[$ext][1]);
        } finally {
            $zip->close();
        }
    }

    private function imageMime(string $head): ?string
    {
        return match (true) {
            str_starts_with($head, "\xFF\xD8\xFF") => 'image/jpeg',
            str_starts_with($head, "\x89PNG\r\n\x1A\n") => 'image/png',
            str_starts_with($head, 'GIF87a'), str_starts_with($head, 'GIF89a') => 'image/gif',
            str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WEBP' => 'image/webp',
            str_starts_with($head, "II*\x00"), str_starts_with($head, "MM\x00*") => 'image/tiff',
            str_starts_with($head, 'BM') => 'image/bmp',
            substr($head, 4, 4) === 'ftyp' && in_array(substr($head, 8, 4), ['heic', 'heix', 'mif1', 'msf1', 'hevc', 'heim', 'heis'], true) => 'image/heic',
            default => null,
        };
    }

    private function text(string $path, string $ext): FileInspection
    {
        $content = (string) file_get_contents($path, false, null, 0, 1 << 20);
        if (str_contains($content, "\x00") || ! mb_check_encoding($content, 'UTF-8')) {
            return FileInspection::reject('mismatch');
        }

        return $ext === 'csv' ? FileInspection::accept('spreadsheet', 'text/csv') : FileInspection::accept('text', 'text/plain');
    }
}
