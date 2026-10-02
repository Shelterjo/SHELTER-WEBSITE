<?php

namespace App\Services\Ai;

/** One AI provider behind the gateway (Anthropic today; OpenAI or another later without touching the assistants). */
interface AiProvider
{
    public function name(): string;

    /** True when the provider is configured (a server-side key exists). */
    public function available(): bool;

    /**
     * A short text answer, or null on any failure (the assistants then answer without AI).
     *
     * @param  list<array{role: 'user'|'assistant', content: string}>  $messages
     */
    public function complete(string $system, array $messages, int $maxTokens): ?string;
}
