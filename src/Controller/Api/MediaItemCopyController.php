<?php

namespace App\Controller\Api;

use App\Entity\MediaItem;
use App\Repository\MediaFolderRepository;
use App\Service\Media\MediaCopyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class MediaItemCopyController extends AbstractController
{
    public function __invoke(
        MediaItem $data,
        Request $request,
        MediaCopyService $copyService,
        MediaFolderRepository $mediaFolderRepository,
        SerializerInterface $serializer,
    ): Response {
        $targetFolder = $data->getFolder();

        $content = $request->getContent();
        if ($content !== '') {
            $payload = json_decode($content, true);
            if (\is_array($payload) && \array_key_exists('folder', $payload) && $payload['folder'] !== null && $payload['folder'] !== '') {
                $folder = $mediaFolderRepository->find((int) $payload['folder']);
                if ($folder === null) {
                    return $this->json(['error' => 'Ordner nicht gefunden.'], Response::HTTP_BAD_REQUEST);
                }
                $targetFolder = $folder;
            }
        }

        try {
            $copy = $copyService->copy($data, $targetFolder);
        } catch (HttpExceptionInterface $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        $json = $serializer->serialize($copy, 'json', ['groups' => ['media_item:read']]);

        return new Response($json, Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
    }
}
