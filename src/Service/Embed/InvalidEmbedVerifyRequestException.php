<?php

namespace App\Service\Embed;

final class InvalidEmbedVerifyRequestException extends \RuntimeException
{
    public function __construct(
        private readonly string $reason,
    ) {
        parent::__construct('Ungültige Embed-Verify-Anfrage.');
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
