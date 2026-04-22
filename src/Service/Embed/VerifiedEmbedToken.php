<?php

namespace App\Service\Embed;

final readonly class VerifiedEmbedToken
{
    public const PURPOSE = 'editing-mode';

    public function __construct(
        public string $subject,
        public int $issuedAt,
        public int $expiresAt,
        public string $audience,
        public string $purpose,
    ) {
    }
}
