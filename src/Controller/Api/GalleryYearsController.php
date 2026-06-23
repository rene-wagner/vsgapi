<?php

namespace App\Controller\Api;

use App\Repository\MediaItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

final class GalleryYearsController extends AbstractController
{
    public function __invoke(MediaItemRepository $mediaItemRepository): JsonResponse
    {
        return $this->json($mediaItemRepository->countPublicGalleryImagesByYear());
    }
}
