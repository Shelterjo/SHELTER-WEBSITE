<?php

namespace Tests\Feature\Shaltoor;

use App\Models\ShaltoorQuestion;
use App\Services\Shaltoor\Shaltoor;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The AI fallback (M69 §24–§27) once the Owner authorizes a provider: only after the data-built answers found nothing,
 * with public facts only, a daily call limit, the key server-side only, and "no answer" whenever the provider is unsure
 * or fails. No real provider is called here (Http::fake); the key is a placeholder.
 */
class ShaltoorAiTest extends TestCase
{
    use RefreshDatabase;

    private const PLACEHOLDER_KEY = 'test-placeholder-not-a-key';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        config(['ai.provider' => 'anthropic', 'ai.anthropic.key' => self::PLACEHOLDER_KEY, 'ai.daily_limits.shaltoor' => 2]);
    }

    private function reply(string $text): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => $text]]])]);
    }

    public function test_the_ai_answers_only_what_the_data_answers_could_not_and_the_key_never_leaves_the_server(): void
    {
        $this->reply('قهوتنا مختصة وبنحضّرها بعناية.');
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $response = $this->postJson('/ar/shaltoor/', ['question' => 'احكيلي عن طريقة تحضير القهوة عندكم'])->assertOk();
        $response->assertJsonPath('topic', 'ai');
        $this->assertStringNotContainsString(self::PLACEHOLDER_KEY, (string) $response->getContent());
        $this->assertTrue((bool) ShaltoorQuestion::query()->value('used_ai'));

        Http::assertSent(function (Request $request): bool {
            $system = (string) ($request->data()['system'] ?? '');

            return $request->hasHeader('x-api-key', self::PLACEHOLDER_KEY)
                && str_contains($system, 'NO_ANSWER') && str_contains($system, 'untrusted');
        });

        // A data question never reaches the provider.
        $this->postJson('/ar/shaltoor/', ['question' => 'ساعات الدوام'])->assertJsonPath('topic', 'hours');
        Http::assertSentCount(1);
    }

    public function test_unsure_failing_or_over_the_limit_means_no_answer(): void
    {
        $shaltoor = app(Shaltoor::class);

        $this->reply('NO_ANSWER');
        $this->assertFalse($shaltoor->answer('What music do you play?', 'en')->answered);

        Http::fake(['api.anthropic.com/*' => Http::response(['error' => 'x'], 500)]);
        $this->assertFalse($shaltoor->answer('What music do you play?', 'en')->answered);

        $this->reply('A story.');
        $this->assertFalse($shaltoor->answer('What music do you play?', 'en')->answered, 'the daily limit (2) is used up');
    }
}
