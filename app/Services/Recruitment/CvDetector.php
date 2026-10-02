<?php

namespace App\Services\Recruitment;

use App\Models\Recruitment\ApplicationAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

/**
 * Local CV detection (RECRUITMENT-SECURITY §8): a deterministic score on the server — no AI, no external service.
 * File name +50 · CV section headings +15 per group (max 30) · email or phone near the top +10 · 1–5 pages +10.
 * Text comes from local tools only (pdftotext/pdfinfo when present, the document XML for DOCX/ODT, plain text),
 * within a size and time limit; nothing in the file is executed. No guessing: one file → it is the CV; otherwise the
 * top file needs the confident score and margin, or the applicant is asked which file is the CV.
 */
final class CvDetector
{
    private const NAME_SIGNALS = ['cv', 'resume', 'résumé', 'curriculum', 'vitae', 'سيرة', 'السيرة الذاتية', 'سيره'];

    private const HEADING_GROUPS = [
        ['education', 'التعليم', 'المؤهلات', 'المؤهل العلمي', 'qualifications'],
        ['experience', 'الخبرات', 'الخبرة', 'الخبرات العملية', 'employment', 'work history'],
        ['skills', 'المهارات'],
        ['objective', 'profile', 'summary', 'الهدف', 'نبذة'],
    ];

    public function score(string $originalName, string $path, string $family): int
    {
        $score = 0;
        $name = mb_strtolower($originalName);
        foreach (self::NAME_SIGNALS as $signal) {
            if (preg_match('/(^|[^a-z])'.preg_quote($signal, '/').'([^a-z]|$)/u', $name) === 1) {
                $score += 50;
                break;
            }
        }

        $text = mb_strtolower($this->text($path, $family));
        if ($text !== '') {
            $groups = 0;
            foreach (self::HEADING_GROUPS as $group) {
                foreach ($group as $heading) {
                    if (str_contains($text, $heading)) {
                        $groups++;
                        break;
                    }
                }
            }
            $score += min(30, $groups * 15);
            $top = mb_substr($text, 0, 600);
            if (preg_match('/[\w.+-]+@[\w-]+\.[\w.]+/u', $top) === 1 || preg_match('/(\+|00)?\d[\d\s-]{7,}\d/', ApplicantInput::digits($top)) === 1) {
                $score += 10;
            }
        }
        $pages = $family === 'pdf' ? $this->pdfPages($path) : null;
        if ($pages !== null && $pages >= 1 && $pages <= 5) {
            $score += 10;
        }

        return min(100, $score);
    }

    /**
     * Decide the primary CV among the files of one form (RECRUITMENT-SECURITY §8).
     *
     * @param  list<ApplicationAttachment>  $files
     * @return array{primary: ?int, state: 'auto_single'|'auto_confident'|'needs_choice'|'none'}
     */
    public static function decide(array $files): array
    {
        if ($files === []) {
            return ['primary' => null, 'state' => 'none'];
        }
        if (count($files) === 1) {
            return ['primary' => $files[0]->id, 'state' => 'auto_single'];
        }
        usort($files, fn (ApplicationAttachment $a, ApplicationAttachment $b): int => [$b->cv_score, $a->id] <=> [$a->cv_score, $b->id]);
        [$first, $second] = [$files[0], $files[1]];
        $confident = $first->cv_score >= (int) config('careers.cv_detection.confident_score')
            && $first->cv_score - $second->cv_score >= (int) config('careers.cv_detection.confident_margin');

        return $confident ? ['primary' => $first->id, 'state' => 'auto_confident'] : ['primary' => null, 'state' => 'needs_choice'];
    }

    private function text(string $path, string $family): string
    {
        try {
            return match ($family) {
                'pdf' => $this->run(['pdftotext', '-l', '5', '-q', $path, '-']),
                'text' => (string) file_get_contents($path, false, null, 0, 200_000),
                'word' => $this->xmlText($path, ['word/document.xml', 'content.xml']),
                default => '',
            };
        } catch (Throwable) {
            return ''; // extraction failed → the file name signal alone decides
        }
    }

    /** @param  list<string>  $entries */
    private function xmlText(string $path, array $entries): string
    {
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            return '';
        }
        try {
            foreach ($entries as $entry) {
                $stat = $zip->statName($entry);
                if ($stat !== false && $stat['size'] <= 5_000_000) {
                    $xml = (string) $zip->getFromName($entry);

                    return html_entity_decode(strip_tags(str_replace(['</w:p>', '</text:p>'], "\n", $xml)), ENT_QUOTES | ENT_XML1);
                }
            }

            return '';
        } finally {
            $zip->close();
        }
    }

    private function pdfPages(string $path): ?int
    {
        $info = $this->run(['pdfinfo', $path]);

        return preg_match('/^Pages:\s+(\d+)/m', $info, $m) === 1 ? (int) $m[1] : null;
    }

    /** @param  list<string>  $command */
    private function run(array $command): string
    {
        $process = new Process($command, null, ['LANG' => 'C.UTF-8'], null, (float) config('careers.uploads.text_extraction_timeout'));
        try {
            $process->run();
        } catch (Throwable) {
            return '';
        }

        return $process->isSuccessful() ? mb_substr($process->getOutput(), 0, 200_000) : '';
    }

    /** Absolute path of a stored attachment on the private disk. */
    public static function path(ApplicationAttachment $attachment): string
    {
        return Storage::disk('careers')->path($attachment->storage_path);
    }
}
