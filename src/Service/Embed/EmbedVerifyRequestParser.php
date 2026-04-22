<?php

namespace App\Service\Embed;

use Symfony\Component\HttpFoundation\Request;

final class EmbedVerifyRequestParser
{
    public function parse(Request $request): string
    {
        $rawContent = $request->getContent();
        if ($rawContent === '') {
            throw new InvalidEmbedVerifyRequestException('missing_body');
        }

        try {
            $payload = json_decode($rawContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new InvalidEmbedVerifyRequestException('malformed_json');
        }

        if (!is_array($payload)) {
            throw new InvalidEmbedVerifyRequestException('invalid_body');
        }

        $token = $payload['embed_token'] ?? null;
        if (!is_string($token) || trim($token) === '') {
            throw new InvalidEmbedVerifyRequestException('missing_token');
        }

        return $token;
    }
}
