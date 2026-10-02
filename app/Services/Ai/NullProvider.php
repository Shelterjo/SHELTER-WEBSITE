<?php

namespace App\Services\Ai;

/** No provider connected (the default until the Owner authorizes one): every call returns null. */
final class NullProvider implements AiProvider
{
    public function name(): string
    {
        return 'none';
    }

    public function available(): bool
    {
        return false;
    }

    public function complete(string $system, array $messages, int $maxTokens): ?string
    {
        return null;
    }
}
