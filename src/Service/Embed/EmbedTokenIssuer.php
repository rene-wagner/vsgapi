<?php

namespace App\Service\Embed;

final class EmbedTokenIssuer
{
    public function __construct(
        private readonly string $secret,
        private readonly int $ttl,
        private readonly string $audience,
    ) {
    }

    public function issue(string $subject): string
    {
        $issuedAt = time();
        $payload = [
            'sub' => $subject,
            'iat' => $issuedAt,
            'exp' => $issuedAt + $this->ttl,
            'aud' => $this->audience,
            'purpose' => VerifiedEmbedToken::PURPOSE,
        ];

        $headerEncoded = $this->base64UrlEncode((string) json_encode([
            'alg' => 'HS256',
            'typ' => 'EMBED',
        ], JSON_THROW_ON_ERROR));
        $payloadEncoded = $this->base64UrlEncode((string) json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->secret, true);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
