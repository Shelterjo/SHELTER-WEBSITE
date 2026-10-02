<?php

namespace Tests\Feature\Auth;

use App\Models\Branch;
use App\Models\Fact;
use App\Models\User;
use App\Services\Dashboard\HoursEditor;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Copy audit F10: the framework's own messages (validation, auth, passwords, pagination) exist in Arabic with the same
 * keys as Laravel 13's English files, so the Arabic dashboard never shows an English sentence. F44: the Owner's
 * approval reads with the branch name and the site's term «ساعات الدوام».
 */
class ArabicFrameworkMessagesTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';

    /**
     * @param  array<array-key, mixed>  $lines
     * @return list<string>
     */
    private function keys(array $lines, string $prefix = ''): array
    {
        $out = [];
        foreach ($lines as $key => $value) {
            $out = is_array($value) && $value !== [] ? [...$out, ...$this->keys($value, $prefix.$key.'.')] : [...$out, $prefix.$key];
        }

        return $out;
    }

    public function test_every_framework_line_exists_in_arabic_with_the_frameworks_keys(): void
    {
        foreach (['validation', 'auth', 'passwords', 'pagination'] as $file) {
            $ar = $this->keys(require lang_path("ar/{$file}.php"));
            // The framework file leaves `attributes` empty; both of ours name the dashboard's fields there.
            $framework = array_diff($this->keys(require base_path("vendor/laravel/framework/src/Illuminate/Translation/lang/en/{$file}.php")), ['attributes']);
            $this->assertSame([], array_values(array_diff($framework, $ar)), "lang/ar/{$file}.php misses a framework line");
            $this->assertSame($this->keys(require lang_path("en/{$file}.php")), $ar, "lang/ar/{$file}.php has the same keys as lang/en/{$file}.php");
            foreach ($ar as $key) {
                $line = (string) __("{$file}.{$key}", [], 'ar');
                if ($key === 'custom.attribute-name.rule-name') {
                    continue; // the framework's own placeholder entry
                }
                $this->assertMatchesRegularExpression('/\p{Arabic}/u', $line, "{$file}.{$key} is Arabic");
            }
        }
    }

    public function test_the_arabic_dashboard_sign_in_shows_arabic_framework_messages(): void
    {
        User::factory()->withTwoFactor(self::SECRET)->create(['email' => 'owner@example.test']);
        $this->post('/dashboard/login', ['email' => 'owner@example.test', 'password' => 'correct horse battery staple'])->assertRedirect('/dashboard/two-factor');

        // Neither a code nor a recovery code: the framework's required_without line, with the fields' Arabic names.
        $this->post('/dashboard/two-factor', [])->assertSessionHasErrors(['code' => 'حقل رمز التحقق مطلوب عند عدم وجود رمز الاسترداد.']);
        $this->post('/dashboard/two-factor', ['code' => str_repeat('1', 13)])->assertSessionHasErrors(['code' => 'يجب ألا يزيد عدد أحرف حقل رمز التحقق على 12.']);
    }

    public function test_messages_read_correctly_whatever_the_count(): void
    {
        app()->setLocale('ar');
        $this->assertSame('يجب ألا يزيد عدد أحرف حقل كلمة المرور على 200.', Validator::make(['password' => str_repeat('a', 201)], ['password' => 'max:200'])->errors()->first('password'));
        $this->assertSame('يجب أن يكون عدد أرقام حقل الرمز 6.', Validator::make(['pin' => '12'], ['pin' => 'digits:6'], [], ['pin' => 'الرمز'])->errors()->first('pin'));
        $this->assertSame('محاولات دخول كثيرة. حاول مرة أخرى بعد 30 ث.', __('auth.throttle', ['seconds' => 30]));
    }

    public function test_the_hours_approval_is_labelled_with_the_branch_name(): void
    {
        $this->seed(MasterDataSeeder::class);
        $owner = User::factory()->withTwoFactor(self::SECRET)->create();
        $drive = Branch::query()->where('code', 'BR-DRIVE')->firstOrFail();
        Fact::query()->where('key', 'hours.BR-DRIVE.regular')->delete(); // a branch whose hours were never approved before

        $rows = array_map(fn (int $day): array => [$day, '08:00', '23:00'], [6, 0, 1, 2, 3, 4, 5]);
        $this->assertSame([], app(HoursEditor::class)->publishWeek($drive, $rows, 'First hours', HoursEditor::fingerprint($drive, $rows), $owner));

        $fact = Fact::query()->where('key', 'hours.BR-DRIVE.regular')->sole();
        $this->assertSame('ساعات الدوام: شلتر كوفي درايف', $fact->label_ar);
        $this->assertSame('Opening hours: SHELTER COFFEE DRIVE', $fact->label_en);
    }
}
