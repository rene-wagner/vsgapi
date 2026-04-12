<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
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
}
