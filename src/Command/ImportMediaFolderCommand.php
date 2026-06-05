<?php

namespace App\Command;

use App\Service\Media\MediaFolderImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

#[AsCommand(
    name: 'app:media:import-folder',
    description: 'Importiert eine bestehende Ordnerstruktur in die Mediathek.',
)]
class ImportMediaFolderCommand extends Command
{
    public function __construct(private readonly MediaFolderImportService $mediaFolderImportService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'Pfad zum Quellordner.')
            ->addOption(
                'apply',
                null,
                InputOption::VALUE_NONE,
                'Aenderungen wirklich ausfuehren. Ohne diese Option wird nur ein Dry-Run angezeigt.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');
        $apply = (bool) $input->getOption('apply');

        if (!is_string($path) || trim($path) === '') {
            $io->error('Bitte gib einen Quellordner an.');

            return Command::FAILURE;
        }

        if (!$apply) {
            $io->warning('Dry-Run: Es werden keine Dateien kopiert und keine Daten gespeichert.');
        }

        try {
            $result = $this->mediaFolderImportService->import(trim($path), $apply);
        } catch (HttpExceptionInterface | \RuntimeException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        foreach ($result['folders'] as $folder) {
            $io->writeln('Ordner: ' . $folder);
        }

        foreach ($result['files'] as $file) {
            $io->writeln('Datei: ' . $file);
        }

        foreach ($result['skipped'] as $file) {
            $io->warning('Uebersprungen: ' . $file);
        }

        $io->success(sprintf(
            '%d Ordner und %d Dateien wurden %s. %d Dateien wurden uebersprungen.',
            count($result['folders']),
            count($result['files']),
            $apply ? 'importiert' : 'geprueft',
            count($result['skipped']),
        ));

        if (!$apply) {
            $io->writeln('Zum Ausfuehren erneut mit <info>--apply</info> starten.');
        }

        return Command::SUCCESS;
    }
}
