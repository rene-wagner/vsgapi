<?php

namespace App\Service\Media;

use enshrined\svgSanitize\Sanitizer;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SvgSanitizerService
{
    public function sanitize(string $svgContent): string
    {
        $sanitizer = new Sanitizer();
        $clean = $sanitizer->sanitize($svgContent);

        if ($clean === false || $clean === '') {
            throw new BadRequestHttpException('Ungültiges SVG-Format.');
        }

        return $clean;
    }
}