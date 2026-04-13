<?php

namespace App\Controller;

use App\Entity\MediaItem;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class MediaFileServeController
{
    public function __construct(
        private readonly string $mediaStorageDir,
    ) {
    }

    #[Route(
        path: '/media/files/{path}',
        name: 'media_file_serve',
        requirements: ['path' => '.+'],
        methods: ['GET'],
    )]
    public function __invoke(string $path): Response
    {
        $base = realpath($this->mediaStorageDir);
        if ($base === false || !is_dir($base)) {
            throw new NotFoundHttpException();
        }

        $relative = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $candidate = $base . DIRECTORY_SEPARATOR . $relative;
        $resolved = realpath($candidate);

        if ($resolved === false || !str_starts_with($resolved, $base)) {
            throw new NotFoundHttpException();
        }

        if (!is_file($resolved)) {
            throw new NotFoundHttpException();
        }

        if (str_ends_with($resolved, '.svg')) {
            $response = new Response(file_get_contents($resolved));
            $response->headers->set('Content-Type', 'image/svg+xml');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('Content-Disposition', 'inline');

            return $response;
        }

        return new BinaryFileResponse($resolved);
    }

    #[Route(
        path: '/media/cropped/{id}',
        name: 'media_file_cropped',
        requirements: ['id' => '\d+'],
        methods: ['GET'],
    )]
    public function cropped(int $id, EntityManagerInterface $entityManager): Response
    {
        $item = $entityManager->getRepository(MediaItem::class)->find($id);
        if ($item === null) {
            throw new NotFoundHttpException();
        }

        if (!$item->isCroppable() || !$item->hasCropData()) {
            return $this->serveOriginal($item->getPath());
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

        return new StreamedResponse(static function () use ($encoded): void {
            echo $encoded->toString();
        }, Response::HTTP_OK, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function serveOriginal(?string $relativePath): Response
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

        return new BinaryFileResponse($resolved);
    }
}
