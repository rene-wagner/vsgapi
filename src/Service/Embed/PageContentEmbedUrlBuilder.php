<?php

namespace App\Service\Embed;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\User\UserInterface;

final class PageContentEmbedUrlBuilder
{
    public function __construct(
        private readonly EmbedTokenIssuer $tokenIssuer,
        #[Autowire('%page_content.iframe_url%')] private readonly string $iframeUrl,
    ) {
    }

    public function build(UserInterface $user): string
    {
        $token = $this->tokenIssuer->issue($user->getUserIdentifier());
        $separator = str_contains($this->iframeUrl, '?') ? '&' : '?';

        return $this->iframeUrl . $separator . http_build_query([
            'embed_token' => $token,
        ]);
    }
}
