<?php

namespace App\Controller\Api;

use App\Service\MembershipApplication\MembershipApplicationPdfService;
use App\Service\MembershipApplication\MembershipApplicationPayloadMapper;
use App\Service\MembershipApplication\MembershipApplicationStoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class MembershipApplicationController extends AbstractController
{
    #[Route('/api/membership-application', name: 'api_membership_application', methods: ['POST'])]
    public function __invoke(
        Request $request,
        MembershipApplicationPayloadMapper $payloadMapper,
        MembershipApplicationPdfService $membershipApplicationPdfService,
        MembershipApplicationStoreService $membershipApplicationStoreService,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json([
                'error' => 'Ungueltige Anfrage.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!is_array($payload)) {
            return $this->json([
                'error' => 'Ungueltige Anfrage.',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $application = $payloadMapper->map($payload);
        } catch (\InvalidArgumentException $exception) {
            return $this->json([
                'error' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $filename = $membershipApplicationPdfService->create($application);
            $membershipApplicationStoreService->store(
                $application,
                $filename,
                $membershipApplicationPdfService->getRelativePath($filename),
            );
        } catch (\RuntimeException) {
            if (isset($filename)) {
                $membershipApplicationPdfService->delete($filename);
            }

            return $this->json([
                'error' => 'Der Aufnahmeantrag konnte nicht erstellt werden.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Throwable) {
            if (isset($filename)) {
                $membershipApplicationPdfService->delete($filename);
            }

            return $this->json([
                'error' => 'Der Aufnahmeantrag konnte nicht gespeichert werden.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'success' => true,
            'url' => $urlGenerator->generate('membership_application_download', [
                'token' => $membershipApplicationPdfService->getToken($filename),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ], Response::HTTP_CREATED);
    }
}
