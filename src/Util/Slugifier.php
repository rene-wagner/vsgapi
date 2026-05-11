<?php

namespace App\Util;

use function Symfony\Component\String\u;

final class Slugifier
{
    public static function slugify(string $value): string
    {
        return u($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->toString();
    }
}
