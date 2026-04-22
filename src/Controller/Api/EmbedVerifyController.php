<?php

namespace App\Controller\Api;

use App\Service\Embed\EmbedEditingAuthorization;
use App\Service\Embed\EmbedTokenVerifier;
use App\Service\Embed\EmbedVerifyRequestParser;
use App\Service\Embed\InvalidEmbedTokenException;
use App\Service\Embed\InvalidEmbedVerifyRequestException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class EmbedVerifyController extends AbstractController
{
    #[Route('/api/embed/verify', name: 'api_embed_verify', methods: ['POST'])]
    public function __invoke(
        Request $request,
        EmbedVerifyRequestParser $requestParser,
        EmbedTokenVerifier $tokenVerifier,
        EmbedEditingAuthorization $authorization,
        LoggerInterface $logger,
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return $this->json([
                'editingMode' => false,
            ]);
        }

        try {
            $token = $requestParser->parse($request);
            $verifiedToken = $tokenVerifier->verify($token);
        } catch (InvalidEmbedVerifyRequestException | InvalidEmbedTokenException $exception) {
            $logger->info('Embed verification denied.', [
                'reason' => $exception->getReason(),
            ]);

            return $this->json([
                'editingMode' => false,
            ]);
        }

        if (!$authorization->allows($user, $verifiedToken)) {
            $logger->info('Embed verification denied.', [
                'reason' => 'subject_mismatch',
            ]);

            return $this->json([
                'editingMode' => false,
            ]);
        }

        return $this->json([
            'editingMode' => true,
        ]);
    }
}
