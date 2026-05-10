<?php

namespace App\Controller\Api;

use App\Service\MembershipApplication\MembershipApplicationPdfService;
use App\Service\MembershipApplication\MembershipApplicationNotificationService;
use App\Service\MembershipApplication\MembershipApplicationPayloadMapper;
use App\Service\MembershipApplication\MembershipApplicationStoreService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MembershipApplicationController extends AbstractController
{
    #[Route('/api/membership-application', name: 'api_membership_application', methods: ['POST'])]
    public function __invoke(
        Request $request,
        MembershipApplicationPayloadMapper $payloadMapper,
        MembershipApplicationPdfService $membershipApplicationPdfService,
        MembershipApplicationStoreService $membershipApplicationStoreService,
        MembershipApplicationNotificationService $membershipApplicationNotificationService,
        LoggerInterface $logger,
    ): JsonResponse {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json([
                'error' => 'Ungültige Anfrage.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!is_array($payload)) {
            return $this->json([
                'error' => 'Ungültige Anfrage.',
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

            if ($application['isChild']) {
                $token = $membershipApplicationPdfService->getToken($filename);
                $supervisionFilename = $membershipApplicationPdfService->createSupervisionDuty($application, $token);
            }

            $membershipApplicationStoreService->store(
                $application,
                $filename,
                $membershipApplicationPdfService->getRelativePath($filename),
            );
        } catch (\RuntimeException $exception) {
            if (isset($filename)) {
                $membershipApplicationPdfService->delete($filename);
            }

            if (isset($supervisionFilename)) {
                $membershipApplicationPdfService->delete($supervisionFilename);
            }

            $logger->error('Aufnahmeantrag PDF konnte nicht erstellt werden.', [
                'exception' => $exception,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'payload' => $payload,
            ]);

            return $this->json([
                'error' => 'Der Aufnahmeantrag konnte nicht erstellt werden.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Throwable $exception) {
            if (isset($filename)) {
                $membershipApplicationPdfService->delete($filename);
            }

            if (isset($supervisionFilename)) {
                $membershipApplicationPdfService->delete($supervisionFilename);
            }

            $logger->error('Aufnahmeantrag konnte nicht gespeichert werden.', [
                'exception' => $exception,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'payload' => $payload,
                'filename' => $filename ?? null,
            ]);

            return $this->json([
                'error' => 'Der Aufnahmeantrag konnte nicht gespeichert werden.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $membershipApplicationNotificationService->send(
                $application,
                $membershipApplicationPdfService->getAbsolutePath($filename),
                isset($supervisionFilename) ? $membershipApplicationPdfService->getAbsolutePath($supervisionFilename) : null,
            );
        } catch (\Throwable $exception) {
            $logger->error('Aufnahmeantrag E-Mail konnte nicht versendet werden.', [
                'exception' => $exception,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'payload' => $payload,
                'filename' => $filename,
                'supervisionFilename' => $supervisionFilename ?? null,
            ]);

            return $this->json([
                'error' => 'Der Aufnahmeantrag wurde gespeichert, aber die E-Mail konnte nicht versendet werden.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response = [
            'success' => true,
            'url' => $request->getSchemeAndHttpHost() . '/' . ltrim($membershipApplicationPdfService->getRelativePath($filename), '/'),
        ];

        if (isset($supervisionFilename)) {
            $response['supervisionDutyUrl'] = $request->getSchemeAndHttpHost() . '/' . ltrim($membershipApplicationPdfService->getRelativePath($supervisionFilename), '/');
        }

        return $this->json($response, Response::HTTP_CREATED);
    }
}
