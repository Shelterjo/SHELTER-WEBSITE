<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Approved recruitment reference data only (M28): consent text v1 word for word (§21) and the two interview locations
 * (§31). The Jordan city list is NOT seeded — PENDING DATA VERIFICATION (RECRUITMENT-DATA-MODEL §2.12); the form stays
 * closed until the Owner approves a verified list.
 */
class RecruitmentSeeder extends Seeder
{
    public const CONSENT_V1 = 'أقر بأن جميع المعلومات المدخلة في طلب التوظيف صحيحة، وأوافق على قيام SHELTER COFFEE بجمع واستخدام بياناتي لغرض دراسة طلب التوظيف والتواصل معي بشأنه.';

    public function run(): void
    {
        $now = now();
        DB::table('consent_versions')->updateOrInsert(['version' => 'careers-consent-v1'], [
            'text_ar' => self::CONSENT_V1, 'active_from' => $now, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        foreach ([['SHELTER COFFEE DRIVE', 'شلتر كوفي درايف', 1], ['SHELTER COFFEE HOUSE', 'شلتر كوفي هاوس', 2]] as [$en, $ar, $sort]) {
            DB::table('interview_locations')->updateOrInsert(['name_en' => $en], [
                'name_ar' => $ar, 'is_active' => true, 'sort_order' => $sort, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        foreach (['form_version' => config('careers.form_version'), 'active_consent_version' => 'careers-consent-v1'] as $key => $value) {
            DB::table('recruitment_settings')->updateOrInsert(['key' => $key], ['value_json' => json_encode($value), 'created_at' => $now, 'updated_at' => $now]);
        }
    }
}
