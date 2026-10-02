<?php

namespace App\Services\Recruitment;

use RuntimeException;

/**
 * Identity numbers at rest (RECRUITMENT-SECURITY §5): AES-256-GCM with a random 96-bit nonce per value (the GCM tag is
 * appended to the ciphertext), a key version for rotation, and an HMAC-SHA256 blind index under a separate key so
 * duplicates are found without decrypting. Keys are 32 random bytes (base64) from the server environment only.
 */
final class IdentityVault
{
    private const CIPHER = 'aes-256-gcm';

    private const TAG_BYTES = 16;

    public function isConfigured(): bool
    {
        return $this->key((int) config('careers.identity.key_version')) !== null && $this->hmacKey() !== null;
    }

    /** @return array{ciphertext: string, nonce: string, key_version: int} */
    public function encrypt(string $normalized): array
    {
        $version = (int) config('careers.identity.key_version');
        $key = $this->key($version) ?? throw new RuntimeException('Identity encryption key is not configured.');
        $nonce = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($normalized, self::CIPHER, $key, OPENSSL_RAW_DATA, $nonce, $tag, '', self::TAG_BYTES);
        if ($ciphertext === false) {
            throw new RuntimeException('Identity encryption failed.');
        }

        return ['ciphertext' => $ciphertext.$tag, 'nonce' => $nonce, 'key_version' => $version];
    }

    public function decrypt(string $ciphertext, string $nonce, int $keyVersion): string
    {
        $key = $this->key($keyVersion) ?? throw new RuntimeException("Identity key version {$keyVersion} is not available.");
        $plain = openssl_decrypt(substr($ciphertext, 0, -self::TAG_BYTES), self::CIPHER, $key, OPENSSL_RAW_DATA, $nonce, substr($ciphertext, -self::TAG_BYTES));
        if ($plain === false) {
            throw new RuntimeException('Identity decryption failed.');
        }

        return $plain;
    }

    public function blindIndex(string $normalized): string
    {
        $key = $this->hmacKey() ?? throw new RuntimeException('Identity HMAC key is not configured.');

        return hash_hmac('sha256', $normalized, $key);
    }

    /** "********1234" — the only form shown by default (lists, tables, exports). */
    public static function mask(string $last4): string
    {
        return '********'.$last4;
    }

    private function key(int $version): ?string
    {
        if ($version === (int) config('careers.identity.key_version')) {
            return self::decode((string) config('careers.identity.encryption_key'));
        }
        foreach (explode(',', (string) config('careers.identity.previous_keys')) as $entry) {
            [$v, $encoded] = array_pad(explode(':', trim($entry), 2), 2, '');
            if ((int) $v === $version) {
                return self::decode($encoded);
            }
        }

        return null;
    }

    private function hmacKey(): ?string
    {
        return self::decode((string) config('careers.identity.hmac_key'));
    }

    private static function decode(string $encoded): ?string
    {
        $raw = base64_decode(str_starts_with($encoded, 'base64:') ? substr($encoded, 7) : $encoded, true);

        return is_string($raw) && strlen($raw) === 32 ? $raw : null;
    }
}
