<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ContentBlockWriteProtectionSubscriber implements EventSubscriberInterface
{
    private string $allowedOrigin;

    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        #[Autowire('%page_content.iframe_url%')] string $iframeUrl,
    ) {
        $this->allowedOrigin = $this->normalizeOrigin($iframeUrl);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->isProtectedContentBlockWriteRequest($request)) {
            return;
        }

        $this->assertOrigin($request);
        $this->assertCsrfToken($request);
    }

    private function isProtectedContentBlockWriteRequest(Request $request): bool
    {
        if (!\in_array($request->getMethod(), ['POST', 'PATCH'], true)) {
            return false;
        }

        return preg_match('#^/api/content_blocks(?:/[^/]+)?$#', $request->getPathInfo()) === 1;
    }

    private function assertOrigin(Request $request): void
    {
        $origin = $request->headers->get('Origin');
        if ($origin === null || $origin === '') {
            throw new AccessDeniedHttpException('Origin-Header fehlt.');
        }

        if ($this->normalizeOrigin($origin) !== $this->allowedOrigin) {
            throw new AccessDeniedHttpException('Origin ist nicht erlaubt.');
        }
    }

    private function assertCsrfToken(Request $request): void
    {
        $tokenValue = $request->headers->get('X-CSRF-Token');
        if ($tokenValue === null || $tokenValue === '') {
            throw new AccessDeniedHttpException('CSRF-Token fehlt.');
        }

        $token = new CsrfToken('content_block_write', $tokenValue);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new AccessDeniedHttpException('CSRF-Token ist ungültig.');
        }
    }

    private function normalizeOrigin(string $url): string
    {
        $parts = parse_url($url);
        if (!\is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
            throw new \InvalidArgumentException('PAGE_CONTENT_IFRAME_URL muss eine absolute URL sein.');
        }

        $origin = $parts['scheme'] . '://' . $parts['host'];
        if (isset($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }

        return $origin;
    }
}
