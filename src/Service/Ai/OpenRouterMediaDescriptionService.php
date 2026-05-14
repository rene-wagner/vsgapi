<?php

namespace App\Service\Ai;

use App\Entity\MediaItem;
use App\Enum\MediaItemType;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Content\Image;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\TextResult;

class OpenRouterMediaDescriptionService
{
    public function __construct(
        private AgentInterface $mediaItemDescription,
        private string $systemPrompt,
        private string $model,
        private string $storageDir,
    ) {
    }

    public function getSystemPrompt(): string
    {
        return $this->systemPrompt;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getModelUrl(): string
    {
        return 'https://openrouter.ai/' . ltrim($this->model, '/');
    }

    public function describe(MediaItem $item): string
    {
        if ($item->getType() !== MediaItemType::Image) {
            throw new \InvalidArgumentException('Nur Bilder können automatisch beschrieben werden.');
        }

        if (!in_array($item->getMimeType(), ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            throw new \InvalidArgumentException('Dieser Bildtyp wird für die automatische Beschreibung nicht unterstützt.');
        }

        $path = $item->getPath();
        if ($path === null || $path === '') {
            throw new \RuntimeException('Die Bilddatei konnte nicht gefunden werden.');
        }

        $absolutePath = rtrim($this->storageDir, '/') . '/' . ltrim($path, '/');
        if (!is_readable($absolutePath)) {
            throw new \RuntimeException('Die Bilddatei konnte nicht gelesen werden.');
        }

        $result = $this->mediaItemDescription->call(new MessageBag(
            Message::ofUser(
                new Text('Bitte beschreibe dieses Bild für das alt-Attribut einer Website.'),
                Image::fromFile($absolutePath),
            ),
        ));

        if (!$result instanceof TextResult || trim($result->getContent()) === '') {
            throw new \RuntimeException('Die KI hat keinen gültigen Text zurückgegeben.');
        }

        return trim($result->getContent());
    }
}
