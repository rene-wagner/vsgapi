<?php

namespace App\Service\Ai;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\TextResult;

class OpenRouterPostRewriteService
{
    public function __construct(
        private AgentInterface $postRewriteAgent,
        private string $systemPrompt,
    ) {
    }

    public function getSystemPrompt(): string
    {
        return $this->systemPrompt;
    }

    public function rewrite(string $content): string
    {
        $content = trim($content);

        if ($content === '') {
            throw new \InvalidArgumentException('Bitte geben Sie zuerst einen Text ein.');
        }

        $result = $this->postRewriteAgent->call(new MessageBag(
            Message::ofUser($content),
        ));

        if (!$result instanceof TextResult || trim($result->getContent()) === '') {
            throw new \RuntimeException('Die KI hat keinen gültigen Text zurückgegeben.');
        }

        return trim($result->getContent());
    }
}
