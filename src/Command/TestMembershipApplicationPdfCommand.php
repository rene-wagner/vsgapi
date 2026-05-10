<?php

namespace App\Command;

use App\Service\MembershipApplication\MembershipApplicationPayloadMapper;
use App\Service\MembershipApplication\MembershipApplicationPdfService;
use App\Service\MembershipApplication\MembershipApplicationStoreService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-membership-application-pdf',
    description: 'Erzeugt eine Test-PDF fuer den Aufnahmeantrag.',
)]
final class TestMembershipApplicationPdfCommand extends Command
{
    private const TEST_FILENAME = 'aufnahmeantrag-test.pdf';

    public function __construct(
        private readonly MembershipApplicationPayloadMapper $payloadMapper,
        private readonly MembershipApplicationPdfService $pdfService,
        private readonly MembershipApplicationStoreService $storeService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'payload',
            null,
            InputOption::VALUE_REQUIRED,
            'Pfad zu einer JSON-Datei mit Payload fuer den Aufnahmeantrag.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $payload = $this->loadPayload($input->getOption('payload'));
            $application = $this->payloadMapper->map($payload);
            $filename = $this->pdfService->create($application, self::TEST_FILENAME);

            if ($application['isChild']) {
                $supervisionFilename = $this->pdfService->createSupervisionDuty($application, $this->pdfService->getToken($filename), 'aufsichtspflicht-test.pdf');
            }

            $this->storeService->store(
                $application,
                $filename,
                $this->pdfService->getRelativePath($filename),
            );
        } catch (\InvalidArgumentException | \RuntimeException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        } catch (\Throwable) {
            if (isset($filename)) {
                $this->pdfService->delete($filename);
            }

            if (isset($supervisionFilename)) {
                $this->pdfService->delete($supervisionFilename);
            }

            $io->error('Die Test-PDF konnte nicht gespeichert werden.');

            return Command::FAILURE;
        }

        $io->success('Test-PDF wurde erzeugt.');
        $io->writeln('Datei: ' . $filename);
        $io->writeln('Pfad: /' . ltrim($this->pdfService->getRelativePath($filename), '/'));

        if (isset($supervisionFilename)) {
            $io->writeln('Datei Aufsichtspflicht: ' . $supervisionFilename);
            $io->writeln('Pfad Aufsichtspflicht: /' . ltrim($this->pdfService->getRelativePath($supervisionFilename), '/'));
        }

        return Command::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadPayload(mixed $payloadOption): array
    {
        if (!is_string($payloadOption) || trim($payloadOption) === '') {
            return [
                'department' => 'tischtennis',
                'firstName' => 'René',
                'lastName' => 'Wagner',
                'birthDate' => '1987-01-06',
                'phone' => '+4915174562402',
                'email' => 'rene.papenfuss@live.de',
                'street' => 'Große Deichstraße 12',
                'postalCode' => '06667',
                'city' => 'Weißenfels',
                'otherClub' => 'Irgendwo',
                'bankName' => 'Sparkasse Burgenlandkreis',
                'iban' => 'DE86800530001141831755',
                'bic' => 'ABC123456',
                'accountHolder' => 'René Wagner',
                'place' => 'Weißenfels',
                'applicationDate' => '2026-05-06',
                'legalGuardianName' => 'Max Mustermann',
                'acceptsStatutes' => true,
                'acceptsEmailInvitation' => true,
                'acceptsPrivacyPolicy' => true,
                'confirmsMinorAttachment' => true,
                'isChild' => true,
                'guardianOneName' => 'Mustermann, Klaus',
                'guardianOneAddress' => 'Große Deichstraße 12, 06667 Weißenfels',
                'guardianOnePhone' => '+49123456789',
                'guardianTwoName' => 'Mustermann, Karin',
                'guardianTwoAddress' => 'Große Deichstraße 12, 06667 Weißenfels',
                'guardianTwoPhone' => '+49123456789',
                'underTwelveMayWalkHomeAlone' => true,
                'overTwelveMayWalkHomeAlone' => false,
            ];
        }

        $payloadPath = trim($payloadOption);
        if (!is_file($payloadPath) || !is_readable($payloadPath)) {
            throw new \RuntimeException('Die angegebene JSON-Datei konnte nicht gelesen werden.');
        }

        try {
            $payload = json_decode((string) file_get_contents($payloadPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new \RuntimeException('Die JSON-Datei ist ungueltig.');
        }

        if (!is_array($payload)) {
            throw new \RuntimeException('Die JSON-Datei muss ein Objekt enthalten.');
        }

        return $payload;
    }
}
