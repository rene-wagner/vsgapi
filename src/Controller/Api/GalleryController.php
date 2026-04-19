<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\MediaItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class GalleryController extends AbstractController
{
    #[Route('/api/gallery', name: 'api_gallery', methods: ['GET'])]
    public function __invoke(
        Request $request,
        MediaItemRepository $mediaItemRepository,
        CategoryRepository $categoryRepository,
        SerializerInterface $serializer,
    ): Response {
        $page = max(1, (int) $request->query->get('page', '1'));
        $perPage = min(100, max(1, (int) $request->query->get('perPage', '20')));

        $category = null;
        $categoryIri = $request->query->get('category');
        if ($categoryIri !== null && $categoryIri !== '') {
            $categorySlug = $this->parseCategoryIri($categoryIri);
            $category = $categoryRepository->findOneBy(['slug' => $categorySlug]);
            if ($category === null) {
                throw new BadRequestHttpException('Die angegebene Kategorie existiert nicht.');
            }
        }

        $paginator = $mediaItemRepository->findGalleryPaginated($page, $perPage, $category);
        $total = $paginator->count();
        $items = iterator_to_array($paginator);

        $json = $serializer->serialize($items, 'json', ['groups' => ['media_item:read']]);

        return new Response(
            $json,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json',
                'X-Total-Count' => (string) $total,
                'X-Page' => (string) $page,
                'X-Per-Page' => (string) $perPage,
            ],
        );
    }

    private function parseCategoryIri(string $iri): string
    {
        $prefix = '/api/categories/';
        if (!str_starts_with($iri, $prefix)) {
            throw new BadRequestHttpException('Ungültiges Kategorie-IRI-Format. Erwartet: /api/categories/{slug}');
        }

        $slug = substr($iri, strlen($prefix));
        if ($slug === '') {
            throw new BadRequestHttpException('Ungültiges Kategorie-IRI-Format. Erwartet: /api/categories/{slug}');
        }

        return $slug;
    }
}