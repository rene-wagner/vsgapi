<?php

namespace App\Controller\Api;

use App\Repository\CategoryRepository;
use App\Repository\MediaFolderRepository;
use App\Service\Media\MediaUploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class MediaItemUploadController extends AbstractController
{
    public function __invoke(
        Request $request,
        MediaUploadService $uploadService,
        MediaFolderRepository $mediaFolderRepository,
        CategoryRepository $categoryRepository,
        SerializerInterface $serializer,
    ): Response {
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return $this->json(['error' => 'Datei fehlt oder ist ungültig.'], Response::HTTP_BAD_REQUEST);
        }

        $folder = null;
        $folderRaw = $request->request->get('folder');
        if ($folderRaw !== null && $folderRaw !== '') {
            $folder = $mediaFolderRepository->find((int) $folderRaw);
            if ($folder === null) {
                return $this->json(['error' => 'Ordner nicht gefunden.'], Response::HTTP_BAD_REQUEST);
            }
        }

        $category = null;
        $categoryRaw = $request->request->get('category');
        if ($categoryRaw !== null && $categoryRaw !== '') {
            $category = $categoryRepository->find((int) $categoryRaw);
            if ($category === null) {
                return $this->json(['error' => 'Kategorie nicht gefunden.'], Response::HTTP_BAD_REQUEST);
            }
        }

        $description = $request->request->get('description');
        $name = $request->request->get('name');

        try {
            $item = $uploadService->upload(
                $file,
                $folder,
                $category,
                \is_string($description) ? $description : null,
                \is_string($name) ? $name : null,
            );
        } catch (HttpExceptionInterface $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        $json = $serializer->serialize($item, 'json', ['groups' => ['media_item:read']]);

        return new Response($json, Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
    }
}
