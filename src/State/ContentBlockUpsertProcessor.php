<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ContentBlock;
use App\Repository\ContentBlockRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ContentBlockUpsertProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ContentBlockRepository $contentBlockRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ContentBlock
    {
        if (!$data instanceof ContentBlock) {
            throw new \InvalidArgumentException('Expected instance of ' . ContentBlock::class . '.');
        }

        $existingContentBlock = null;
        if ($data->getId() !== null && $data->getUrl() !== null) {
            $existingContentBlock = $this->contentBlockRepository->findOneBy([
                'id' => $data->getId(),
                'url' => $data->getUrl(),
            ]);
        }

        if ($existingContentBlock instanceof ContentBlock) {
            $existingContentBlock->setContent($data->getContent());

            $this->entityManager->flush();

            return $existingContentBlock;
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
