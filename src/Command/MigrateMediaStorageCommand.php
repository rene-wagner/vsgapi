<?php

namespace App\Command;

use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use App\Repository\MediaFolderRepository;
use App\Repository\MediaItemRepository;
use App\Service\Media\MediaStorageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

#[AsCommand(
    name: 'app:media:migrate-storage',
    description: 'Migriert bestehende Medien-Dateien in die Ordnerstruktur.',
)]
class MigrateMediaStorageCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MediaFolderRepository $mediaFolderRepository,
        private readonly MediaItemRepository $mediaItemRepository,
        private readonly MediaStorageService $mediaStorageService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'apply',
            null,
            InputOption::VALUE_NONE,
            'Aenderungen wirklich ausfuehren. Ohne diese Option wird nur ein Dry-Run angezeigt.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $apply = (bool) $input->getOption('apply');

        if (!$apply) {
            $io->warning('Dry-Run: Es werden keine Dateien verschoben und keine Daten gespeichert.');
        }

        try {
            $folderChanges = $this->migrateFolders($io, $apply);
            $itemChanges = $this->migrateItems($io, $apply);

            if ($apply) {
                $this->entityManager->flush();
            }
        } catch (HttpExceptionInterface | \RuntimeException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf(
            '%d Ordner und %d Medien-Eintraege wurden %s.',
            $folderChanges,
            $itemChanges,
            $apply ? 'migriert' : 'geprueft',
        ));

        if (!$apply) {
            $io->writeln('Zum Ausfuehren erneut mit <info>--apply</info> starten.');
        }

        return Command::SUCCESS;
    }

    private function migrateFolders(SymfonyStyle $io, bool $apply): int
    {
        $count = 0;

        foreach ($this->mediaFolderRepository->findBy([], ['name' => 'ASC']) as $folder) {
            $targetPath = $this->mediaStorageService->buildFolderPath($folder);
            if ($folder->getStoragePath() !== $targetPath) {
                $io->writeln(sprintf('Ordnerpfad: %s -> %s', $folder->getStoragePath() ?? '(leer)', $targetPath));
                if ($apply) {
                    $folder->setStoragePath($targetPath);
                }
                ++$count;
            }

            if ($apply) {
                $this->mediaStorageService->ensureFolderExists($folder);
            }
        }

        return $count;
    }

    private function migrateItems(SymfonyStyle $io, bool $apply): int
    {
        $count = 0;

        foreach ($this->mediaItemRepository->findBy([], ['id' => 'ASC']) as $item) {
            $targetPath = $this->buildTargetItemPath($item);
            $sourcePath = $item->getPath();

            if ($sourcePath === null || $sourcePath === '') {
                $io->warning(sprintf('Medien-Eintrag %d hat keinen Dateipfad.', $item->getId()));
                continue;
            }

            $changed = false;
            if ($sourcePath !== $targetPath) {
                $io->writeln(sprintf('Datei: %s -> %s', $sourcePath, $targetPath));
                if ($apply) {
                    $this->moveIfNeeded($sourcePath, $targetPath, true);
                    $item->setPath($targetPath);
                }
                $changed = true;
            }

            $targetThumbnailPath = $this->mediaStorageService->buildThumbnailRelativePath($item->getFolder(), basename($targetPath));
            $sourceThumbnailPath = $this->resolveSourceThumbnailPath($item, $sourcePath);
            if ($sourceThumbnailPath !== null && $sourceThumbnailPath !== $targetThumbnailPath) {
                $io->writeln(sprintf('Thumbnail: %s -> %s', $sourceThumbnailPath, $targetThumbnailPath));
                if ($apply) {
                    $this->moveIfNeeded($sourceThumbnailPath, $targetThumbnailPath, false);
                    $item->setThumbnailPath($targetThumbnailPath);
                }
                $changed = true;
            }

            if ($sourceThumbnailPath === null && $item->getThumbnailPath() !== null && $item->getThumbnailPath() !== '') {
                $io->warning(sprintf('Thumbnail fehlt: %s', $item->getThumbnailPath()));
            }

            if ($changed) {
                ++$count;
            }
        }

        return $count;
    }

    private function buildTargetItemPath(MediaItem $item): string
    {
        $sourcePath = $item->getPath() ?? '';
        $extension = $item->getExtension() ?? pathinfo($sourcePath, PATHINFO_EXTENSION);
        $identifier = $this->extractIdentifier(pathinfo($sourcePath, PATHINFO_FILENAME), $item);
        $filename = $this->mediaStorageService->buildMediaFilename($item->getName() ?? 'medium', $identifier, $extension);

        return $this->mediaStorageService->buildItemRelativePath($item->getFolder(), $filename);
    }

    private function extractIdentifier(string $filename, MediaItem $item): string
    {
        if (preg_match('/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$/i', $filename, $matches) === 1) {
            return strtolower($matches[1]);
        }

        return (string) $item->getId();
    }

    private function resolveSourceThumbnailPath(MediaItem $item, string $sourcePath): ?string
    {
        $thumbnailPath = $item->getThumbnailPath();
        if ($thumbnailPath !== null && $thumbnailPath !== '' && is_file($this->mediaStorageService->absolutePath($thumbnailPath))) {
            return $thumbnailPath;
        }

        $legacyPath = 'thumbnails/' . pathinfo($sourcePath, PATHINFO_FILENAME) . '.jpg';
        if (is_file($this->mediaStorageService->absolutePath($legacyPath))) {
            return $legacyPath;
        }

        return null;
    }

    private function moveIfNeeded(string $sourcePath, string $targetPath, bool $required): void
    {
        $sourceAbsolutePath = $this->mediaStorageService->absolutePath($sourcePath);
        $targetAbsolutePath = $this->mediaStorageService->absolutePath($targetPath);

        if ($sourceAbsolutePath === $targetAbsolutePath) {
            return;
        }

        if (is_file($targetAbsolutePath) && !is_file($sourceAbsolutePath)) {
            return;
        }

        $this->mediaStorageService->moveStorageFile($sourcePath, $targetPath, $required);
    }
}
