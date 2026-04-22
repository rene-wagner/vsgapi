<?php

namespace App\Service\Embed;

final class EmbedTokenVerifier
{
    public function __construct(
        private readonly string $secret,
        private readonly string $audience,
    ) {
    }

    public function verify(string $token): VerifiedEmbedToken
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new InvalidEmbedTokenException('invalid_format');
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $signature = $this->base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->secret, true);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new InvalidEmbedTokenException('invalid_signature');
        }

        $payloadJson = $this->base64UrlDecode($payloadEncoded);

        try {
            $payload = json_decode($payloadJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new InvalidEmbedTokenException('invalid_payload');
        }

        if (!is_array($payload)) {
            throw new InvalidEmbedTokenException('invalid_payload');
        }

        if (!isset($payload['sub']) || !is_string($payload['sub']) || $payload['sub'] === '') {
            throw new InvalidEmbedTokenException('missing_subject');
        }

        if (($payload['aud'] ?? null) !== $this->audience) {
            throw new InvalidEmbedTokenException('invalid_audience');
        }

        if (($payload['purpose'] ?? null) !== VerifiedEmbedToken::PURPOSE) {
            throw new InvalidEmbedTokenException('invalid_purpose');
        }

        if (!isset($payload['iat'], $payload['exp']) || !is_int($payload['iat']) || !is_int($payload['exp'])) {
            throw new InvalidEmbedTokenException('invalid_timestamps');
        }

        if ($payload['exp'] < time()) {
            throw new InvalidEmbedTokenException('expired');
        }

        return new VerifiedEmbedToken(
            $payload['sub'],
            $payload['iat'],
            $payload['exp'],
            $payload['aud'],
            $payload['purpose'],
        );
    }

    private function base64UrlDecode(string $value): string
    {
        $paddedValue = strtr($value, '-_', '+/');
        $padding = strlen($paddedValue) % 4;

        if ($padding > 0) {
            $paddedValue .= str_repeat('=', 4 - $padding);
        }

        $decodedValue = base64_decode($paddedValue, true);
        if ($decodedValue === false) {
            throw new InvalidEmbedTokenException('invalid_encoding');
        }

        return $decodedValue;
    }
}
