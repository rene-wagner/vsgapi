<?php

namespace App\Controller\Api;

use App\Entity\ContactPerson;
use App\Repository\ContactPersonRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ContactController extends AbstractController
{
    #[Route('/api/contact', name: 'api_contact', methods: ['POST'])]
    public function __invoke(
        Request $request,
        ContactPersonRepository $contactPersonRepository,
        MailerInterface $mailer,
        CsrfTokenManagerInterface $csrfTokenManager,
        #[Autowire(service: 'limiter.contact_form')] RateLimiterFactory $contactFormLimiter,
    ): JsonResponse {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json([
                'error' => 'Ungültige Anfrage.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!\is_array($payload)) {
            return $this->json([
                'error' => 'Ungültige Anfrage.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $limit = $contactFormLimiter->create($request->getClientIp() ?? 'unknown')->consume();
        if (!$limit->isAccepted()) {
            return $this->json([
                'error' => 'Zu viele Anfragen. Bitte versuchen Sie es später erneut.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $contactPersonId = $payload['contactPersonId'] ?? null;
        $csrfToken = isset($payload['csrfToken']) && \is_string($payload['csrfToken']) ? trim($payload['csrfToken']) : '';
        $senderName = isset($payload['senderName']) && \is_string($payload['senderName']) ? trim($payload['senderName']) : '';
        $senderEmail = isset($payload['senderEmail']) && \is_string($payload['senderEmail']) ? trim($payload['senderEmail']) : '';
        $subject = isset($payload['subject']) && \is_string($payload['subject']) ? trim($payload['subject']) : '';
        $message = isset($payload['message']) && \is_string($payload['message']) ? trim($payload['message']) : '';
        $website = isset($payload['website']) && \is_string($payload['website']) ? trim($payload['website']) : '';

        if ($website !== '') {
            return $this->json([
                'success' => true,
            ]);
        }

        if ($csrfToken === '') {
            return $this->json([
                'error' => 'CSRF-Token fehlt.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('contact_form_submit', $csrfToken))) {
            return $this->json([
                'error' => 'CSRF-Token ist ungültig.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!\is_int($contactPersonId) && !ctype_digit((string) $contactPersonId)) {
            return $this->json([
                'error' => 'Ungültige Kontaktperson.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ((int) $contactPersonId <= 0 || $senderName === '' || $subject === '' || $message === '') {
            return $this->json([
                'error' => 'Bitte füllen Sie alle Pflichtfelder aus.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (filter_var($senderEmail, FILTER_VALIDATE_EMAIL) === false) {
            return $this->json([
                'error' => 'Bitte geben Sie eine gültige E-Mail-Adresse an.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $contactPerson = $contactPersonRepository->find((int) $contactPersonId);
        if (!$contactPerson instanceof ContactPerson) {
            return $this->json([
                'error' => 'Kontaktperson nicht gefunden.',
            ], Response::HTTP_NOT_FOUND);
        }

        $recipientEmail = $contactPerson->getEmail();
        if ($recipientEmail === null || trim($recipientEmail) === '') {
            return $this->json([
                'error' => 'Für diese Kontaktperson ist keine E-Mail-Adresse hinterlegt.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $recipientName = trim(($contactPerson->getFirstName() ?? '') . ' ' . ($contactPerson->getLastName() ?? ''));

        $mailerFrom = $this->getParameter('app.mailer_from');
        if (!\is_string($mailerFrom) || trim($mailerFrom) === '') {
            throw new \LogicException('app.mailer_from muss gesetzt sein.');
        }

        $email = (new TemplatedEmail())
            ->from(new Address($mailerFrom, 'VSG Kontaktformular'))
            ->to(new Address($recipientEmail, $recipientName !== '' ? $recipientName : $recipientEmail))
            ->replyTo(new Address($senderEmail, $senderName))
            ->subject('Kontaktformular: ' . $subject)
            ->htmlTemplate('contact/email.html.twig')
            ->context([
                'contactPerson' => $contactPerson,
                'senderName' => $senderName,
                'senderEmail' => $senderEmail,
                'subject' => $subject,
                'message' => $message,
            ])
        ;

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface) {
            return $this->json([
                'error' => 'Die Nachricht konnte nicht gesendet werden.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'success' => true,
        ]);
    }
}
