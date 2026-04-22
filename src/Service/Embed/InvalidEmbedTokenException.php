<?php

namespace App\Service\Embed;

final class InvalidEmbedTokenException extends \RuntimeException
{
    public function __construct(
        private readonly string $reason,
    ) {
        parent::__construct('Ungültiger Embed-Token.');
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
