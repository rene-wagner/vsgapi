<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class MembershipApplicationDownloadController
{
    public function __construct(
        private readonly string $storageDir,
    ) {
    }

    #[Route(
        path: '/aufnahmeantrag/{token}.pdf',
        name: 'membership_application_download',
        requirements: ['token' => '(?:\d{8}-[a-f0-9]{32}|test)'],
        methods: ['GET'],
    )]
    public function __invoke(string $token): BinaryFileResponse
    {
        $base = realpath($this->storageDir);
        if ($base === false || !is_dir($base)) {
            throw new NotFoundHttpException();
        }

        $filename = $this->resolveFilename($base, $token);
        if ($filename === null) {
            throw new NotFoundHttpException();
        }

        $absolutePath = $base . DIRECTORY_SEPARATOR . $filename;
        $resolved = realpath($absolutePath);
        if ($resolved === false || !str_starts_with($resolved, $base) || !is_file($resolved)) {
            throw new NotFoundHttpException();
        }

        $response = new BinaryFileResponse($resolved);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }

    private function resolveFilename(string $base, string $token): ?string
    {
        $filenames = [
            'aufnahmeantrag-' . $token . '.pdf',
            'membership-application-' . $token . '.pdf',
        ];

        foreach ($filenames as $filename) {
            if (is_file($base . DIRECTORY_SEPARATOR . $filename)) {
                return $filename;
            }
        }

        return null;
    }
}
