<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Anthropic Messages API (server-side; the key comes from config ai.anthropic.key). Failures return null, logged without content. */
final class AnthropicProvider implements AiProvider
{
    public function name(): string
    {
        return 'anthropic';
    }

    public function available(): bool
    {
        return is_string(config('ai.anthropic.key')) && config('ai.anthropic.key') !== '';
    }

    public function complete(string $system, array $messages, int $maxTokens): ?string
    {
        if (! $this->available()) {
            return null;
        }
        try {
            $response = Http::withHeaders([
                'x-api-key' => (string) config('ai.anthropic.key'),
                'anthropic-version' => (string) config('ai.anthropic.version'),
            ])->timeout((int) config('ai.timeout_seconds', 12))->acceptJson()->post((string) config('ai.anthropic.endpoint'), [
                'model' => (string) config('ai.anthropic.model'),
                'max_tokens' => $maxTokens,
                'system' => $system,
                'messages' => $messages,
            ]);
            if (! $response->successful()) {
                Log::warning('ai.provider_failed', ['provider' => 'anthropic', 'status' => $response->status()]);

                return null;
            }
            $text = collect((array) $response->json('content'))->where('type', 'text')->pluck('text')->implode("\n");

            return trim($text) === '' ? null : trim($text);
        } catch (Throwable $e) {
            Log::warning('ai.provider_failed', ['provider' => 'anthropic', 'error' => $e::class]);

            return null;
        }
    }
}
