<?php

namespace Tests\Feature\Media;

use App\Models\Media;
use App\Services\Media\MediaLibrary;
use App\Services\Media\MediaRights;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

/** MEDIA-RIGHTS §3 (one publication rule) and §7 acceptance tests MR-T02…MR-T08, plus MEDIA-011 (web copies). */
class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
        Storage::fake('media_public');
    }

    /**
     * A plain test image; each seed gives a different file (and so a different asset).
     *
     * @param  int<1, max>  $width
     * @param  int<1, max>  $height
     */
    public static function imageFile(int $width = 1000, int $height = 800, int $seed = 0): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, (int) imagecolorallocate($image, max(0, min(255, 120 + $seed)), 90, 60));
        $path = tempnam(sys_get_temp_dir(), 'img').'.png';
        imagepng($image, $path);

        return $path;
    }

    private int $images = 0;

    /**
     * A distinct approved asset per call (the same file would be the same asset — MR-T08).
     *
     * @param  array<string, mixed>  $attributes
     */
    private function approved(array $attributes = []): Media
    {
        $media = app(MediaLibrary::class)->import(self::imageFile(seed: ++$this->images), ['source' => 'shelter', 'people_consent' => 'none', 'alt_ar' => 'صورة', 'alt_en' => 'Photo']);
        $media->forceFill($attributes + ['approval_status' => Media::APPROVED, 'approved_at' => now(), 'ok_website' => true])->save();

        return $media->refresh();
    }

    public function test_an_imported_file_waits_for_approval_and_the_same_file_is_one_asset(): void
    {
        $library = app(MediaLibrary::class);
        $path = self::imageFile();
        $media = $library->import($path, ['source' => 'shelter']);

        $this->assertSame('MED-00001', $media->code);
        $this->assertSame('PENDING OWNER APPROVAL', $media->approval_status);
        $this->assertFalse($media->ok_website);
        $this->assertSame([1000, 800], [$media->width, $media->height]);
        Storage::disk('media')->assertExists($media->original_path);
        $this->assertSame($media->id, $library->import($path, ['source' => 'contracted'])->id, 'MR-T08: same sha256 → same asset');
        $this->assertSame(1, Media::query()->count());

        $this->expectException(InvalidArgumentException::class);
        $library->import($path.'.missing', ['source' => 'shelter']);
    }

    public function test_the_publication_rule(): void
    {
        $now = CarbonImmutable::now();
        $this->assertTrue(MediaRights::canUse($this->approved()));
        $this->assertFalse(MediaRights::canUse($this->approved(['approval_status' => 'PENDING OWNER APPROVAL'])), 'MR-T02');
        $this->assertFalse(MediaRights::canUse($this->approved(['people_consent' => 'not_recorded'])), 'MR-T03');
        $this->assertFalse(MediaRights::canUse($this->approved(['ok_ads' => false]), 'ads'), 'MR-T04');
        $this->assertFalse(MediaRights::canUse($this->approved(['ok_website' => false])));
        $this->assertFalse(MediaRights::canUse($this->approved(['rights_expires_at' => $now->subDay()])), 'expired');
        $this->assertTrue(MediaRights::canUse($this->approved(['rights_expires_at' => $now->addDays(10)])));
        $this->assertFalse(MediaRights::canUse($this->approved(['source' => 'stock'])), 'MR-T07');
        $this->assertTrue(MediaRights::canUse($this->approved(['source' => 'stock', 'source_explicitly_approved' => true])));
        $this->assertFalse(MediaRights::canUse($this->approved(['archived_at' => $now])));
        $this->assertFalse(MediaRights::canUse(null));
    }

    public function test_people_must_consent_for_the_channel_and_a_withdrawal_takes_the_asset_down(): void
    {
        $person = ['person' => 'P-1', 'consented_at' => '2026-10-01', 'scopes' => ['website'], 'document_ref' => 'paper file 12'];
        $media = $this->approved(['people_consent' => 'recorded', 'people_consents' => [$person], 'ok_ads' => true]);
        $this->assertTrue(MediaRights::canUse($media, 'website'));
        $this->assertFalse(MediaRights::canUse($media, 'ads'), 'consent covers the website only');

        $media->forceFill(['people_consents' => [$person + ['withdrawn_at' => '2026-10-02']]])->save();
        $this->assertFalse(MediaRights::canUse($media->refresh()), 'MR-T05: withdrawn → not usable anywhere');
        $this->assertNull(app(MediaLibrary::class)->image($media, 'ar'));
    }

    public function test_web_copies_exist_only_for_approved_assets_and_carry_their_alt_text(): void
    {
        $library = app(MediaLibrary::class);
        $pending = $library->import(self::imageFile(seed: 100), ['source' => 'shelter', 'people_consent' => 'none']);
        $this->assertFalse($library->generateVariants($pending), 'MEDIA-011');
        $this->assertSame([], Storage::disk('media_public')->allFiles());

        $media = $this->approved();
        $this->assertTrue($library->generateVariants($media));
        $webp = $media->refresh()->variants['image/webp'] ?? [];
        $this->assertSame([480, 800, 1000], array_column($webp, 'width'), 'never upscaled beyond the original');
        foreach ($webp as $copy) {
            Storage::disk('media_public')->assertExists($copy['path']);
        }

        $image = $library->image($media, 'ar');
        $this->assertNotNull($image);
        $this->assertSame('صورة', $image->alt);
        $this->assertSame([1000, 800], [$image->width, $image->height]);
        $this->assertStringContainsString('-480.webp 480w', $image->sources['image/webp']);

        $media->forceFill(['alt_en' => null])->save();
        $this->assertNull($library->image($media->refresh(), 'en'), 'no alternative text, no image (MEDIA-006)');
        $this->assertNotNull($library->image($media, 'en', 'Name of the person'));
    }
}
