<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ContactFormCsrfTokenController extends AbstractController
{
    #[Route('/api/contact/token', name: 'api_contact_form_csrf_token', methods: ['GET'])]
    public function __invoke(CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        return $this->json([
            'token' => $csrfTokenManager->getToken('contact_form_submit')->getValue(),
        ]);
    }
}
