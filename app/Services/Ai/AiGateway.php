<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Cache;

/**
 * The one door to an AI provider (M69 §24, M70 §49): picks the configured provider, enforces each profile's daily
 * call limit (cost control), and never throws — a null answer means "answer without AI". Prompts and data scopes stay
 * with each assistant; the gateway never sees more than the assistant passes in.
 */
final class AiGateway
{
    public const PROFILES = ['shaltoor', 'owner'];

    public function provider(): AiProvider
    {
        return match (config('ai.provider')) {
            'anthropic' => app(AnthropicProvider::class),
            default => app(NullProvider::class),
        };
    }

    public function connected(): bool
    {
        return $this->provider()->available();
    }

    /** @param list<array{role: 'user'|'assistant', content: string}> $messages */
    public function ask(string $profile, string $system, array $messages, int $maxTokens = 400): ?string
    {
        if (! in_array($profile, self::PROFILES, true) || ! $this->connected()) {
            return null;
        }
        $key = 'ai.calls.'.$profile.'.'.now()->format('Y-m-d');
        $limit = (int) config('ai.daily_limits.'.$profile, 0);
        if ($limit <= 0 || (int) Cache::get($key, 0) >= $limit) {
            return null;
        }
        Cache::add($key, 0, now()->endOfDay());
        Cache::increment($key);

        return $this->provider()->complete($system, $messages, $maxTokens);
    }

    /** Calls used today by a profile (shown in the Owner's AI health panel). */
    public function usedToday(string $profile): int
    {
        return (int) Cache::get('ai.calls.'.$profile.'.'.now()->format('Y-m-d'), 0);
    }
}
