<?php

namespace App\Service\Embed;

use Symfony\Component\Security\Core\User\UserInterface;

final class EmbedEditingAuthorization
{
    public function allows(UserInterface $user, VerifiedEmbedToken $token): bool
    {
        return $user->getUserIdentifier() === $token->subject;
    }
}
