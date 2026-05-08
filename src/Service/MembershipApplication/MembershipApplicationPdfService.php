<?php

namespace App\Service\MembershipApplication;

use setasign\Fpdi\Fpdi;

/** @phpstan-import-type MembershipApplicationData from MembershipApplicationPayloadMapper */
final class MembershipApplicationPdfService
{
    public function __construct(
        private readonly string $templatePath,
        private readonly string $storageDir,
    ) {
    }

    /** @param MembershipApplicationData $application */
    public function create(array $application, ?string $filename = null): string
    {
        $this->ensureStorageDirectoryExists();

        $filename ??= sprintf(
            'aufnahmeantrag-%s-%s.pdf',
            date('Ymd'),
            bin2hex(random_bytes(16)),
        );

        $this->getToken($filename);

        $outputPath = $this->storageDir . DIRECTORY_SEPARATOR . $filename;

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        $pdf->setSourceFile($this->templatePath);
        $templateId = $pdf->importPage(1);
        $templateSize = $pdf->getTemplateSize($templateId);
        if (!\is_array($templateSize)) {
            throw new \RuntimeException('Die PDF-Vorlage konnte nicht verarbeitet werden.');
        }

        $pdf->AddPage($templateSize['orientation'], [$templateSize['width'], $templateSize['height']]);
        $pdf->useTemplate($templateId);

        $this->render($pdf, $application);
        $pdf->Output('F', $outputPath);

        return $filename;
    }

    public function getRelativePath(string $filename): string
    {
        return 'antraege/' . $filename;
    }

    public function getToken(string $filename): string
    {
        if (preg_match('/^aufnahmeantrag-((?:\d{8}-[a-f0-9]{32}|test))\.pdf$/', $filename, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/^membership-application-((?:\d{8}-[a-f0-9]{32}|test))\.pdf$/', $filename, $matches) === 1) {
            return $matches[1];
        }

        throw new \InvalidArgumentException('Der Dateiname fuer den Aufnahmeantrag ist ungueltig.');
    }

    public function delete(string $filename): void
    {
        $path = $this->storageDir . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function ensureStorageDirectoryExists(): void
    {
        if (is_dir($this->storageDir)) {
            return;
        }

        if (!mkdir($concurrentDirectory = $this->storageDir, 0775, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException('Das Zielverzeichnis fuer Aufnahmeantraege konnte nicht erstellt werden.');
        }
    }

    /** @param MembershipApplicationData $application */
    private function render(Fpdi $pdf, array $application): void
    {
        $fullName = trim($application['firstName'] . ' ' . $application['lastName']);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Helvetica', '', 9);

        $this->drawDepartment($pdf, $application['department']);

        $this->drawText($pdf, 15, 70, $application['lastName']);
        $this->drawText($pdf, 64, 70, $application['firstName']);
        $this->drawText($pdf, 115, 70, $application['birthDate']);
        $this->drawText($pdf, 148, 70, $application['phone']);

        $this->drawText($pdf, 15, 82, $application['street']);
        $this->drawText($pdf, 93, 82, $application['postalCode']);
        $this->drawText($pdf, 123, 82, $application['city']);

        // $pdf->SetFont('Helvetica', '', 8.5);
        $this->drawText($pdf, 36, 94, $application['email']);
        $this->drawText($pdf, 80, 102, $application['otherClub']);

        $pdf->SetFont('Helvetica', 'B', 10);
        $this->drawCheckbox($pdf, $application['acceptsPrivacyPolicy'] ? 168.5 : 183, 152);

        $pdf->SetFont('Helvetica', 'B', 9);
        $this->drawText($pdf, 15, 171, $application['place']);
        $this->drawText($pdf, 58, 171, $application['applicationDate']);
        $this->drawText($pdf, 92, 171, $fullName);
        $this->drawText($pdf, 144, 171, $application['legalGuardianName']);

        $this->drawText($pdf, 15, 248, $application['bankName']);
        $this->drawText($pdf, 95, 248, $application['iban']);
        $this->drawText($pdf, 160, 248, $application['bic']);

        $this->drawText($pdf, 15, 263, $application['place']);
        $this->drawText($pdf, 58, 263, $application['applicationDate']);
        $this->drawText($pdf, 92, 263, $application['accountHolder']);

        if ($application['confirmsMinorAttachment']) {
            $pdf->SetFont('Helvetica', 'B', 10);
            $this->drawCheckbox($pdf, 168.5, 161);
        }
    }

    private function drawDepartment(Fpdi $pdf, string $department): void
    {
        $positions = [
            'volleyball' => [46.5, 60.5],
            'gymnastik' => [80.5, 60.5],
            'tischtennis' => [116.5, 60.5],
            'badminton' => [153.5, 60.5],
        ];

        if (!isset($positions[$department])) {
            return;
        }

        $pdf->SetFont('Helvetica', 'B', 10);
        $this->drawCheckbox($pdf, $positions[$department][0], $positions[$department][1]);
    }

    private function drawCheckbox(Fpdi $pdf, float $x, float $y): void
    {
        $pdf->Text($x, $y, 'X');
    }

    private function drawText(Fpdi $pdf, float $x, float $y, string $value): void
    {
        $value = trim($value);
        if ($value === '') {
            return;
        }

        $pdf->Text($x, $y, $this->encode($value));
    }

    private function encode(string $value): string
    {
        $encoded = iconv('UTF-8', 'windows-1252//TRANSLIT', $value);

        return $encoded === false ? $value : $encoded;
    }
}
