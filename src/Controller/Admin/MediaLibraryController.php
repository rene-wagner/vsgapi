<?php

namespace App\Controller\Admin;

use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use App\Enum\MediaItemType;
use App\Form\MediaItemEditType;
use App\Form\MediaItemUploadType;
use App\Repository\MediaFolderRepository;
use App\Repository\MediaItemRepository;
use App\Service\Media\MediaCopyService;
use App\Service\Media\MediaDeleteService;
use App\Service\Media\MediaImageCropService;
use App\Service\Media\MediaUploadService;
use App\Service\Media\MediaUrlService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/mediathek')]
class MediaLibraryController extends AbstractController
{
    #[Route('', name: 'admin_mediathek_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        MediaItemRepository $mediaItemRepository,
        MediaFolderRepository $mediaFolderRepository,
        MediaUploadService $mediaUploadService,
        MediaUrlService $mediaUrlService,
    ): Response {
        $folderId = $request->query->get('folder');
        $currentFolder = null;
        if ($folderId !== null && $folderId !== '') {
            $currentFolder = $mediaFolderRepository->find((int) $folderId);
        }

        $uploadForm = $this->createForm(MediaItemUploadType::class, null);
        $uploadForm->handleRequest($request);

        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $filesRaw = $uploadForm->get('files')->getData();
            /** @var list<UploadedFile> $files */
            $files = array_values(array_filter(
                \is_array($filesRaw) ? $filesRaw : [],
                static fn (mixed $f): bool => $f instanceof UploadedFile && $f->isValid(),
            ));
            $category = $uploadForm->get('category')->getData();

            $successCount = 0;
            /** @var list<string> $errorMessages */
            $errorMessages = [];
            foreach ($files as $file) {
                try {
                    $mediaUploadService->upload(
                        $file,
                        $currentFolder,
                        $category,
                        null,
                        null,
                    );
                    ++$successCount;
                } catch (HttpExceptionInterface $e) {
                    $errorMessages[] = $file->getClientOriginalName() . ': ' . $e->getMessage();
                }
            }

            if ($successCount > 0) {
                $this->addFlash(
                    'success',
                    $successCount === 1
                        ? '1 Datei wurde erfolgreich hochgeladen.'
                        : sprintf('%d Dateien wurden erfolgreich hochgeladen.', $successCount),
                );
            }
            if ($errorMessages !== []) {
                $this->addFlash(
                    'danger',
                    'Fehler bei folgenden Dateien: ' . implode(' · ', $errorMessages),
                );
            }
            if ($successCount === 0 && $errorMessages === []) {
                $this->addFlash('danger', 'Es wurden keine gültigen Dateien übermittelt.');
            }

            $params = [];
            if ($currentFolder !== null) {
                $params['folder'] = $currentFolder->getId();
            }

            return $this->redirectToRoute('admin_mediathek_index', $params);
        }

        $items = $mediaItemRepository->findByFolderOrdered($currentFolder);

        return $this->render('admin/mediathek/index.html.twig', [
            'current_folder' => $currentFolder,
            'items' => $items,
            'folders_at_level' => $mediaFolderRepository->findByParentOrdered($currentFolder),
            'folder_breadcrumb' => $currentFolder !== null ? $this->buildFolderBreadcrumbTrail($currentFolder) : [],
            'upload_form' => $uploadForm,
            'media_url' => $mediaUrlService,
        ]);
    }

    #[Route('/items/{id}/edit', name: 'admin_mediathek_item_edit', methods: ['GET', 'POST'])]
    public function editItem(
        Request $request,
        MediaItem $item,
        EntityManagerInterface $entityManager,
        MediaUrlService $mediaUrlService,
        MediaImageCropService $mediaImageCropService,
    ): Response {
        $originalPath = $item->getOriginalPath();
        $showRestoreOriginal = $item->getType() === MediaItemType::Image
            && $originalPath !== null
            && $originalPath !== '';

        $form = $this->createForm(MediaItemEditType::class, $item, [
            'show_restore_original' => $showRestoreOriginal,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->has('restore_original') && $form->get('restore_original')->isClicked()) {
                if ($form->isValid()) {
                    $restoreSucceeded = true;
                    try {
                        $mediaImageCropService->restoreOriginal($item);
                    } catch (\InvalidArgumentException $e) {
                        $form->addError(new FormError($e->getMessage()));
                        $restoreSucceeded = false;
                    }

                    if ($restoreSucceeded) {
                        $entityManager->flush();
                        $this->addFlash('success', 'Das Originalbild wurde wiederhergestellt.');

                        $params = [];
                        if ($item->getFolder() !== null) {
                            $params['folder'] = $item->getFolder()->getId();
                        }

                        return $this->redirectToRoute('admin_mediathek_index', $params);
                    }
                }
            } elseif ($form->isValid()) {
                $cropPayload = $this->parseCropPayload($form);
                if ($cropPayload === false) {
                    $form->addError(new FormError('Die Zuschnitt-Daten sind unvollständig oder ungültig.'));
                } elseif ($cropPayload !== null) {
                    if ($item->getType() !== MediaItemType::Image) {
                        $form->addError(new FormError('Zuschneiden ist nur für Bilddateien möglich.'));
                    } else {
                        try {
                            $mediaImageCropService->applyCrop(
                                $item,
                                $cropPayload['x'],
                                $cropPayload['y'],
                                $cropPayload['w'],
                                $cropPayload['h'],
                                $cropPayload['nw'],
                                $cropPayload['nh'],
                            );
                        } catch (\InvalidArgumentException $e) {
                            $form->addError(new FormError($e->getMessage()));
                        }
                    }
                }

                if ($form->isValid()) {
                    $entityManager->flush();
                    $this->addFlash('success', 'Medien-Eintrag wurde aktualisiert.');

                    $params = [];
                    if ($item->getFolder() !== null) {
                        $params['folder'] = $item->getFolder()->getId();
                    }

                    return $this->redirectToRoute('admin_mediathek_index', $params);
                }
            }
        }

        return $this->render('admin/mediathek/edit.html.twig', [
            'item' => $item,
            'form' => $form,
            'media_url' => $mediaUrlService,
            'show_restore_original' => $showRestoreOriginal,
        ]);
    }

    /**
     * @return array{x: int, y: int, w: int, h: int, nw: int, nh: int}|null|false
     */
    private function parseCropPayload(FormInterface $form): array|false|null
    {
        $names = ['crop_x', 'crop_y', 'crop_w', 'crop_h', 'crop_natural_w', 'crop_natural_h'];
        $parts = [];
        foreach ($names as $name) {
            $v = $form->get($name)->getData();
            if ($v === null || $v === '') {
                $parts[] = null;
            } elseif (is_numeric($v)) {
                $parts[] = (int) $v;
            } else {
                return false;
            }
        }

        $allEmpty = !array_filter($parts, static fn (?int $p): bool => $p !== null);
        if ($allEmpty) {
            return null;
        }

        if (in_array(null, $parts, true)) {
            return false;
        }

        return [
            'x' => $parts[0],
            'y' => $parts[1],
            'w' => $parts[2],
            'h' => $parts[3],
            'nw' => $parts[4],
            'nh' => $parts[5],
        ];
    }

    #[Route('/items/{id}/copy', name: 'admin_mediathek_item_copy', methods: ['POST'])]
    public function copyItem(
        Request $request,
        MediaItem $item,
        MediaCopyService $mediaCopyService,
        MediaFolderRepository $mediaFolderRepository,
    ): Response {
        if (!$this->isCsrfTokenValid('copy_media_item' . $item->getId(), $request->getPayload()->getString('_token'))) {
            return $this->redirectWithFolder($item->getFolder());
        }

        $targetFolder = $item->getFolder();
        $folderId = $request->getPayload()->get('folder_id');
        if ($folderId !== null && $folderId !== '') {
            $f = $mediaFolderRepository->find((int) $folderId);
            if ($f !== null) {
                $targetFolder = $f;
            }
        }

        try {
            $mediaCopyService->copy($item, $targetFolder);
            $this->addFlash('success', 'Medien-Eintrag wurde kopiert.');
        } catch (HttpExceptionInterface $e) {
            $this->addFlash('danger', $e->getMessage());

            return $this->redirectWithFolder($item->getFolder());
        }

        return $this->redirectWithFolder($targetFolder);
    }

    #[Route('/items/{id}/delete', name: 'admin_mediathek_item_delete', methods: ['POST'])]
    public function deleteItem(
        Request $request,
        MediaItem $item,
        MediaDeleteService $mediaDeleteService,
    ): Response {
        if (!$this->isCsrfTokenValid('delete_media_item' . $item->getId(), $request->getPayload()->getString('_token'))) {
            return $this->redirectWithFolder($item->getFolder());
        }

        $folder = $item->getFolder();
        $mediaDeleteService->delete($item);
        $this->addFlash('success', 'Medien-Eintrag wurde gelöscht.');

        return $this->redirectWithFolder($folder);
    }

    /**
     * @return list<MediaFolder>
     */
    private function buildFolderBreadcrumbTrail(MediaFolder $folder): array
    {
        $chain = [];
        while ($folder !== null) {
            $chain[] = $folder;
            $folder = $folder->getParent();
        }

        return array_reverse($chain);
    }

    private function redirectWithFolder(?MediaFolder $folder): Response
    {
        $params = [];
        if ($folder !== null) {
            $params['folder'] = $folder->getId();
        }

        return $this->redirectToRoute('admin_mediathek_index', $params);
    }
}
