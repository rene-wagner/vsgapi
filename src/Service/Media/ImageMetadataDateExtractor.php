<?php

namespace App\Service\Media;

class ImageMetadataDateExtractor
{
    private const DATE_FIELDS = [
        'DateTimeOriginal',
        'CreateDate',
        'DateTimeDigitized',
        'DateTime',
    ];

    public function extract(string $absolutePath, string $mimeType): ?\DateTimeImmutable
    {
        if (!\in_array($mimeType, ['image/jpeg', 'image/tiff'], true)) {
            return null;
        }

        if (!\function_exists('exif_read_data') || !is_file($absolutePath) || !is_readable($absolutePath)) {
            return null;
        }

        $exif = @exif_read_data($absolutePath, null, true, false);
        if (!\is_array($exif)) {
            return null;
        }

        foreach (self::DATE_FIELDS as $field) {
            $value = $this->findExifValue($exif, $field);
            if ($value === null) {
                continue;
            }

            $date = $this->parseExifDate($value);
            if ($date !== null) {
                return $date;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $exif */
    private function findExifValue(array $exif, string $field): ?string
    {
        foreach ($exif as $section) {
            if (!\is_array($section) || !isset($section[$field]) || !\is_string($section[$field])) {
                continue;
            }

            $value = trim($section[$field]);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function parseExifDate(string $value): ?\DateTimeImmutable
    {
        if (preg_match('/^(\d{4}):(\d{2}):(\d{2}) (\d{2}):(\d{2}):(\d{2})$/', $value) !== 1) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y:m:d H:i:s', $value);
        if (!$date instanceof \DateTimeImmutable) {
            return null;
        }

        $errors = \DateTimeImmutable::getLastErrors();
        if (\is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            return null;
        }

        return $date;
    }
}
