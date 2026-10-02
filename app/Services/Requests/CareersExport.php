<?php

namespace App\Services\Requests;

use App\Models\Recruitment\Application;
use App\Services\Recruitment\IdentityVault;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Exports of job applications (CAREERS-072/073, INFRA-035/036): CSV and Excel built here without third-party code,
 * PDF as a print-ready page the browser saves (correct Arabic shaping — G35). Identity numbers are masked unless the
 * Owner explicitly asks for them; spreadsheet cells never start a formula (=, +, -, @, tab, CR); text is UTF-8. The
 * attachments go in a separate ZIP, never inside an export. The caller audits every export (who, what, how many).
 */
final class CareersExport
{
    /** A ZIP holds at most this many applications (a request stays within the server's limits — G10-TF-18). */
    public const ZIP_MAX_APPLICATIONS = 50;

    /** Columns in export order => label key (dashboard.requests.fields.*). */
    private const COLUMNS = ['reference', 'submitted', 'status', 'name', 'phone', 'email', 'gender', 'nationality', 'identity', 'city', 'area',
        'job', 'education', 'experience', 'same_field', 'employed', 'license', 'salary'];

    public function __construct(private readonly IdentityVault $vault) {}

    /**
     * The table: a header row, then one row per application, in the page language.
     *
     * @param  iterable<Application>  $applications
     * @return list<list<string>>
     */
    public function rows(iterable $applications, bool $fullIdentity): array
    {
        $rows = [array_map(fn (string $c): string => (string) __('dashboard.requests.fields.'.$c), self::COLUMNS)];
        $ar = app()->getLocale() === 'ar';
        $option = fn (string $group, string $value): string => (string) __('dashboard.requests.options.'.$group.'.'.$value);
        foreach ($applications as $application) {
            $job = $application->job;
            if ($job === null) {
                continue;
            }
            $identity = $application->identity;
            $id = $identity === null ? '' : ($fullIdentity
                ? (string) $this->vault->decrypt($identity->id_ciphertext, $identity->id_nonce, $identity->id_key_version)
                : IdentityVault::mask($identity->id_last4));
            $rows[] = [
                $application->reference_number,
                CarbonImmutable::instance($application->submitted_at)->setTimezone('Asia/Amman')->format('Y-m-d H:i'),
                (string) __('dashboard.requests.statuses.'.$application->status),
                $job->full_name,
                $job->phone_normalized,
                $job->email,
                $option('gender', $job->gender),
                $job->nationality_type === 'jordanian' ? $option('nationality_type', 'jordanian') : trim($option('nationality_type', $job->nationality_type).' '.($job->nationality_text ?? '')),
                $id,
                $job->city !== null ? ($ar ? $job->city->name_ar : ($job->city->name_en ?? $job->city->name_ar)) : '',
                $job->area_text,
                $job->job_title_text,
                $option('education_level', $job->education_level),
                $option('experience_band', $job->experience_band),
                $option('yes_no', $job->same_field_experience ? '1' : '0'),
                $option('yes_no', $job->currently_employed ? '1' : '0'),
                $option('yes_no', $job->has_driving_license ? '1' : '0'),
                rtrim(rtrim((string) $job->expected_salary_jod, '0'), '.'),
            ];
        }

        return $rows;
    }

    /** A cell that a spreadsheet would read as a formula is turned into plain text (CSV / formula injection). */
    public static function safe(string $value): string
    {
        $value = str_replace("\0", '', $value);

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }

    /** @param  list<list<string>>  $rows */
    public function csv(array $rows): string
    {
        $out = fopen('php://temp', 'r+') ?: throw new RuntimeException('No temporary stream.');
        fwrite($out, "\u{FEFF}"); // UTF-8 BOM: Excel opens Arabic correctly
        foreach ($rows as $row) {
            fputcsv($out, array_map(self::safe(...), $row), ',', '"', '');
        }
        rewind($out);
        $csv = (string) stream_get_contents($out);
        fclose($out);

        return $csv;
    }

    /**
     * A minimal Office Open XML workbook (one sheet, right-to-left for Arabic, shared nothing — inline strings only).
     *
     * @param  list<list<string>>  $rows
     * @return string path of a temporary .xlsx file (the caller deletes it after sending)
     */
    public function xlsx(array $rows, bool $rtl): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx') ?: throw new RuntimeException('No temporary file.');
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Cannot write the workbook.');
        }
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
        $zip->addFromString('[Content_Types].xml', $xml.'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
        $zip->addFromString('_rels/.rels', $xml.'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', $xml.'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Applications" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', $xml.'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $zip->addFromString('xl/styles.xml', $xml.'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Arial"/></font><font><b/><sz val="11"/><name val="Arial"/></font></fonts>'
            .'<fills count="1"><fill><patternFill patternType="none"/></fill></fills><borders count="1"><border/></borders>'
            .'<cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="2"><xf fontId="0"/><xf fontId="1" applyFont="1"/></cellXfs></styleSheet>');

        $sheet = $xml.'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"'.($rtl ? ' rightToLeft="1"' : '').'>'
            .'<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><sheetData>';
        foreach ($rows as $r => $row) {
            $sheet .= '<row r="'.($r + 1).'">';
            foreach ($row as $c => $value) {
                $ref = self::column($c).($r + 1);
                $text = htmlspecialchars(self::safe($value), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $sheet .= '<c r="'.$ref.'" t="inlineStr"'.($r === 0 ? ' s="1"' : '').'><is><t xml:space="preserve">'.$text.'</t></is></c>';
            }
            $sheet .= '</row>';
        }
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet.'</sheetData></worksheet>');
        $zip->close();

        return $path;
    }

    /**
     * The attachments of the chosen applications in one ZIP: a folder per application number (never the applicant's
     * name in a path), each file under a safe name.
     *
     * @param  iterable<Application>  $applications
     * @return array{path: string, files: int}
     */
    public function attachmentsZip(iterable $applications, string $disk = 'careers'): array
    {
        $path = tempnam(sys_get_temp_dir(), 'zip') ?: throw new RuntimeException('No temporary file.');
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Cannot write the archive.');
        }
        $files = 0;
        $storage = Storage::disk($disk);
        foreach ($applications as $application) {
            foreach ($application->attachments as $n => $attachment) {
                if (! $storage->exists($attachment->storage_path)) {
                    continue;
                }
                $name = preg_replace('/[^\p{L}\p{N}._-]+/u', '_', pathinfo($attachment->original_filename, PATHINFO_FILENAME)) ?: 'file';
                $zip->addFile($storage->path($attachment->storage_path), $application->reference_number.'/'.($n + 1).'-'.mb_substr($name, 0, 60).'.'.$attachment->extension);
                $files++;
            }
        }
        if ($files === 0) {
            $zip->addFromString('README.txt', 'No files.');
        }
        $zip->close();

        return ['path' => $path, 'files' => $files];
    }

    /** 0 → A, 25 → Z, 26 → AA … */
    private static function column(int $index): string
    {
        $name = '';
        for ($i = $index; $i >= 0; $i = intdiv($i, 26) - 1) {
            $name = chr(65 + $i % 26).$name;
        }

        return $name;
    }
}
