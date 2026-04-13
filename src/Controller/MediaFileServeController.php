<?php

namespace App\Controller;

use App\Entity\MediaItem;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsController]
final class MediaFileServeController
{
    public function __construct(
        private readonly string $mediaStorageDir,
        private readonly int $thumbnailMaxEdge,
    ) {
    }

    #[Route(
        path: '/media/{id}-{slug}.{ext}',
        name: 'media_original',
        requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+', 'ext' => '[a-z0-9]+'],
        methods: ['GET'],
    )]
    public function original(int $id, string $slug, string $ext, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
    {
        $item = $entityManager->getRepository(MediaItem::class)->find($id);
        if ($item === null) {
            throw new NotFoundHttpException();
        }

        if ($slug !== $item->getSlug() || $ext !== $item->getExtension()) {
            return new RedirectResponse(
                $urlGenerator->generate('media_original', [
                    'id' => $item->getId(),
                    'slug' => $item->getSlug(),
                    'ext' => $item->getExtension(),
                ]),
                Response::HTTP_MOVED_PERMANENTLY,
            );
        }

        return $this->serveOriginalFile($item->getPath());
    }

    #[Route(
        path: '/media/{id}-{slug}/thumbnail.jpg',
        name: 'media_thumbnail',
        requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'],
        methods: ['GET'],
    )]
    public function thumbnail(int $id, string $slug, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
    {
        $item = $entityManager->getRepository(MediaItem::class)->find($id);
        if ($item === null) {
            throw new NotFoundHttpException();
        }

        if ($slug !== $item->getSlug()) {
            return new RedirectResponse(
                $urlGenerator->generate('media_thumbnail', [
                    'id' => $item->getId(),
                    'slug' => $item->getSlug(),
                ]),
                Response::HTTP_MOVED_PERMANENTLY,
            );
        }

        $thumbnailPath = $item->getThumbnailPath();
        if ($thumbnailPath === null || $thumbnailPath === '') {
            throw new NotFoundHttpException();
        }

        return $this->serveOriginalFile($thumbnailPath);
    }

    #[Route(
        path: '/media/{id}-{slug}/cropped.{ext}',
        name: 'media_cropped',
        requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+', 'ext' => '[a-z0-9]+'],
        methods: ['GET'],
    )]
    public function cropped(int $id, string $slug, string $ext, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
    {
        $item = $entityManager->getRepository(MediaItem::class)->find($id);
        if ($item === null) {
            throw new NotFoundHttpException();
        }

        if ($slug !== $item->getSlug() || $ext !== $item->getExtension()) {
            return new RedirectResponse(
                $urlGenerator->generate('media_cropped', [
                    'id' => $item->getId(),
                    'slug' => $item->getSlug(),
                    'ext' => $item->getExtension(),
                ]),
                Response::HTTP_MOVED_PERMANENTLY,
            );
        }

        if (!$item->isCroppable() || !$item->hasCropData()) {
            return $this->serveOriginalFile($item->getPath());
        }

        $base = realpath($this->mediaStorageDir);
        if ($base === false || !is_dir($base)) {
            throw new NotFoundHttpException();
        }

        $absolutePath = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $item->getPath());
        $resolved = realpath($absolutePath);
        if ($resolved === false || !str_starts_with($resolved, $base) || !is_file($resolved)) {
            throw new NotFoundHttpException();
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read($resolved);
        $image->crop(
            (int) $item->getCropWidth(),
            (int) $item->getCropHeight(),
            (int) $item->getCropX(),
            (int) $item->getCropY(),
        );

        $mimeType = $item->getMimeType() ?? 'image/jpeg';
        $encoded = match ($mimeType) {
            'image/png' => $image->toPng(),
            'image/webp' => $image->toWebp(quality: 82),
            default => $image->toJpeg(quality: 82),
        };

        $response = new StreamedResponse(static function () use ($encoded): void {
            echo $encoded->toString();
        }, Response::HTTP_OK, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);

        $lastModified = $item->getUpdatedAt();
        if ($lastModified !== null) {
            $response->setLastModified($lastModified);
        }

        return $response;
    }

    #[Route(
        path: '/media/{id}-{slug}/cropped/thumbnail.jpg',
        name: 'media_cropped_thumbnail',
        requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'],
        methods: ['GET'],
    )]
    public function croppedThumbnail(int $id, string $slug, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
    {
        $item = $entityManager->getRepository(MediaItem::class)->find($id);
        if ($item === null) {
            throw new NotFoundHttpException();
        }

        if ($slug !== $item->getSlug()) {
            return new RedirectResponse(
                $urlGenerator->generate('media_cropped_thumbnail', [
                    'id' => $item->getId(),
                    'slug' => $item->getSlug(),
                ]),
                Response::HTTP_MOVED_PERMANENTLY,
            );
        }

        if (!$item->isCroppable() || !$item->hasCropData()) {
            throw new NotFoundHttpException();
        }

        $base = realpath($this->mediaStorageDir);
        if ($base === false || !is_dir($base)) {
            throw new NotFoundHttpException();
        }

        $absolutePath = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $item->getPath());
        $resolved = realpath($absolutePath);
        if ($resolved === false || !str_starts_with($resolved, $base) || !is_file($resolved)) {
            throw new NotFoundHttpException();
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read($resolved);
        $image->crop(
            (int) $item->getCropWidth(),
            (int) $item->getCropHeight(),
            (int) $item->getCropX(),
            (int) $item->getCropY(),
        );
        $image->scaleDown(width: $this->thumbnailMaxEdge, height: $this->thumbnailMaxEdge);

        $encoded = $image->toJpeg(quality: 82);

        $response = new StreamedResponse(static function () use ($encoded): void {
            echo $encoded->toString();
        }, Response::HTTP_OK, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);

        $lastModified = $item->getUpdatedAt();
        if ($lastModified !== null) {
            $response->setLastModified($lastModified);
        }

        return $response;
    }

    private function serveOriginalFile(?string $relativePath): Response
    {
        if ($relativePath === null || $relativePath === '') {
            throw new NotFoundHttpException();
        }

        $base = realpath($this->mediaStorageDir);
        if ($base === false || !is_dir($base)) {
            throw new NotFoundHttpException();
        }

        $absolutePath = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $resolved = realpath($absolutePath);
        if ($resolved === false || !str_starts_with($resolved, $base) || !is_file($resolved)) {
            throw new NotFoundHttpException();
        }

        if (str_ends_with($resolved, '.svg')) {
            $response = new Response(file_get_contents($resolved));
            $response->headers->set('Content-Type', 'image/svg+xml');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('Content-Disposition', 'inline');
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');

            return $response;
        }

        $response = new BinaryFileResponse($resolved);
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
