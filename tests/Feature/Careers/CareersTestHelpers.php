<?php

namespace Tests\Feature\Careers;

use App\Models\Recruitment\JordanCity;
use Database\Seeders\RecruitmentSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Shared setup for the careers tests: random identity keys generated per run (no key is ever committed), a fake
 * private disk, the approved recruitment seed, and SAMPLE cities clearly marked as test data (the real list is
 * PENDING DATA VERIFICATION and never invented).
 */
trait CareersTestHelpers
{
    protected function bootCareers(bool $withCities = true): void
    {
        config([
            'careers.identity.encryption_key' => base64_encode(random_bytes(32)),
            'careers.identity.hmac_key' => base64_encode(random_bytes(32)),
            'careers.identity.key_version' => 1,
            'careers.uploads.max_file_bytes' => 2 * 1024 * 1024,
        ]);
        Storage::fake('careers');
        $this->seed(RecruitmentSeeder::class);
        if ($withCities) {
            foreach (['مدينة تجريبية أ', 'مدينة تجريبية ب'] as $i => $name) {
                JordanCity::query()->create(['name_ar' => $name, 'sort_order' => $i, 'source' => 'TEST DATA', 'verification_status' => 'TEST DATA']);
            }
        }
    }

    protected function cityId(): int
    {
        return (int) JordanCity::query()->orderBy('id')->value('id');
    }

    /**
     * A complete, valid Jordanian application (sample data).
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validInput(array $overrides = []): array
    {
        return $overrides + [
            'full_name' => 'متقدم تجريبي Test',
            'phone' => '0791234567',
            'email' => 'Applicant.Test@Example.com',
            'gender' => 'female',
            'birth_day' => '29',
            'birth_month' => '2',
            'birth_year' => '2000',
            'marital_status' => 'single',
            'nationality_type' => 'jordanian',
            'national_id' => '9991234567',
            'city_id' => (string) $this->cityId(),
            'area' => 'منطقة تجريبية',
            'job_title' => 'Barista باريستا',
            'education_level' => 'bachelor',
            'experience_band' => 'y1_2',
            'same_field_experience' => 'yes',
            'currently_employed' => 'no',
            'expected_salary' => '٤٥٠',
            'has_driving_license' => 'no',
            'notes' => "سطر أول\nSecond line",
            'consent' => '1',
        ];
    }

    /** A small valid one-page PDF whose text local tools can read (sample content). */
    protected function pdf(string $name = 'cv.pdf', string $body = 'Education Experience Skills applicant@example.com'): UploadedFile
    {
        $stream = 'BT /F1 12 Tf 72 720 Td ('.$body.') Tj ET';
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";

        return UploadedFile::fake()->createWithContent($name, $pdf);
    }

    protected function png(string $name = 'photo.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "\x89PNG\r\n\x1A\n".str_repeat("\x00", 32));
    }

    protected function docx(string $name = 'letter.docx', bool $macro = false): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'docx');
        $zip = new \ZipArchive;
        $zip->open($path, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<Types><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('word/document.xml', '<w:document><w:body><w:p><w:t>Cover letter</w:t></w:p></w:body></w:document>');
        if ($macro) {
            $zip->addFromString('word/vbaProject.bin', 'VBA');
        }
        $zip->close();

        return UploadedFile::fake()->createWithContent($name, (string) file_get_contents($path));
    }
}
