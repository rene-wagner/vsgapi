<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class CsrfTokenController extends AbstractController
{
    #[Route('/api/content-blocks/csrf-token', name: 'api_content_block_csrf_token', methods: ['GET'])]
    public function contentBlocks(CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->json([
            'token' => $csrfTokenManager->getToken('content_block_write')->getValue(),
        ]);
    }

    #[Route('/api/contact-form/csrf-token', name: 'api_contact_form_csrf_token', methods: ['GET'])]
    public function contactForm(CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        return $this->json([
            'token' => $csrfTokenManager->getToken('contact_form_submit')->getValue(),
        ]);
    }
}
