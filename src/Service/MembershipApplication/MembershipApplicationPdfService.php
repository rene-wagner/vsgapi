<?php

namespace App\Service\MembershipApplication;

use setasign\Fpdi\Fpdi;

/** @phpstan-import-type MembershipApplicationData from MembershipApplicationPayloadMapper */
final class MembershipApplicationPdfService
{
    public function __construct(
        private readonly string $templatePath,
        private readonly string $supervisionTemplatePath,
        private readonly string $storageDir,
    ) {
    }

    /** @param MembershipApplicationData $application */
    public function create(array $application, ?string $filename = null): string
    {
        $filename ??= $this->buildFilename('aufnahmeantrag');

        return $this->createFromTemplate($application, $this->templatePath, $filename, $this->render(...));
    }

    /** @param MembershipApplicationData $application */
    public function createSupervisionDuty(array $application, string $token, ?string $filename = null): string
    {
        $filename ??= $this->buildFilename('aufsichtspflicht', $token);

        return $this->createFromTemplate($application, $this->supervisionTemplatePath, $filename, $this->renderSupervisionDuty(...));
    }

    public function getRelativePath(string $filename): string
    {
        return 'antraege/' . $filename;
    }

    public function getToken(string $filename): string
    {
        if (preg_match('/^(?:aufnahmeantrag|membership-application|aufsichtspflicht)-((?:\d{8}-[a-f0-9]{32}|test))\.pdf$/', $filename, $matches) === 1) {
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

    private function buildFilename(string $prefix, ?string $token = null): string
    {
        $token ??= sprintf(
            '%s-%s',
            date('Ymd'),
            bin2hex(random_bytes(16)),
        );

        return sprintf('%s-%s.pdf', $prefix, $token);
    }

    /**
     * @param MembershipApplicationData $application
     * @param callable(Fpdi, array): void $renderer
     */
    private function createFromTemplate(array $application, string $templatePath, string $filename, callable $renderer): string
    {
        $this->ensureStorageDirectoryExists();
        $this->getToken($filename);

        $outputPath = $this->storageDir . DIRECTORY_SEPARATOR . $filename;

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        $pdf->setSourceFile($templatePath);
        $templateId = $pdf->importPage(1);
        $templateSize = $pdf->getTemplateSize($templateId);
        if (!\is_array($templateSize)) {
            throw new \RuntimeException('Die PDF-Vorlage konnte nicht verarbeitet werden.');
        }

        $pdf->AddPage($templateSize['orientation'], [$templateSize['width'], $templateSize['height']]);
        $pdf->useTemplate($templateId);

        $renderer($pdf, $application);
        $pdf->Output('F', $outputPath);

        return $filename;
    }

    /** @param MembershipApplicationData $application */
    private function render(Fpdi $pdf, array $application): void
    {
        $fullName = trim($application['firstName'] . ' ' . $application['lastName']);

        $pdf->SetTextColor(0, 128, 0);
        $pdf->SetFont('Helvetica', 'B', 9);

        $this->drawDepartment($pdf, $application['department']);

        $this->drawText($pdf, 15, 70, $application['lastName']);
        $this->drawText($pdf, 64, 70, $application['firstName']);
        $this->drawText($pdf, 115, 70, $application['birthDate']);
        $this->drawText($pdf, 148, 70, $application['phone']);

        $this->drawText($pdf, 15, 82, $application['street']);
        $this->drawText($pdf, 93, 82, $application['postalCode']);
        $this->drawText($pdf, 123, 82, $application['city']);

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

    /** @param MembershipApplicationData $application */
    private function renderSupervisionDuty(Fpdi $pdf, array $application): void
    {
        $fullName = trim($application['firstName'] . ' ' . $application['lastName']);
        $address = trim(sprintf('%s, %s %s', $application['street'], $application['postalCode'], $application['city']), ', ');
        $signature = trim($application['legalGuardianName']);

        if ($signature === '') {
            $signature = trim(implode(' / ', array_filter([
                $application['guardianOneName'],
                $application['guardianTwoName'],
            ])));
        }

        $pdf->SetTextColor(0, 128, 0);
        $pdf->SetFont('Helvetica', 'B', 10);

        $this->drawText($pdf, 20, 35, $fullName);
        $this->drawText($pdf, 127, 35, $application['birthDate']);
        $this->drawText($pdf, 20, 44.5, $address);

        $pdf->SetFont('Helvetica', 'B', 10);
        $this->drawDepartment($pdf, $application['department'], 43.5, 79.5, 36.5);

        $pdf->SetFont('Helvetica', 'B', 10);
        $this->drawText($pdf, 51, 102.8, $application['guardianOneName']);
        $this->drawText($pdf, 51, 112.5, $application['guardianOneAddress']);
        $this->drawText($pdf, 51, 122, $application['guardianOnePhone']);

        $this->drawText($pdf, 51, 137, $application['guardianTwoName']);
        $this->drawText($pdf, 51, 146.5, $application['guardianTwoAddress']);
        $this->drawText($pdf, 51, 156.5, $application['guardianTwoPhone']);

        $pdf->SetFont('Helvetica', 'B', 10);
        $this->drawCheckbox($pdf, $application['underTwelveMayWalkHomeAlone'] ? 73.5 : 123, 215);
        $this->drawCheckbox($pdf, $application['overTwelveMayWalkHomeAlone'] ? 73.5 : 123, 250);

        $pdf->SetFont('Helvetica', 'B', 10);
        $this->drawText($pdf, 20, 268.5, sprintf('%s, %s', $application['place'], $application['applicationDate']));
    }

    private function drawDepartment(Fpdi $pdf, string $department, float $x = 46.5, float $y = 60.5, float $spacing = 34): void
    {
        $positions = [
            'volleyball' => [$x, $y],
            'gymnastik' => [$x + $spacing, $y],
            'tischtennis' => [$x + (2 * $spacing), $y],
            'badminton' => [$x + (3 * $spacing), $y],
        ];

        if (!isset($positions[$department])) {
            return;
        }

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
