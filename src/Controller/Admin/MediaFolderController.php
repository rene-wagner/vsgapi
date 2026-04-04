<?php

namespace App\Controller\Admin;

use App\Entity\MediaFolder;
use App\Form\MediaFolderType;
use App\Repository\MediaFolderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/media-folders')]
class MediaFolderController extends AbstractController
{
    #[Route('', name: 'admin_media_folder_index', methods: ['GET'])]
    public function index(MediaFolderRepository $mediaFolderRepository): Response
    {
        return $this->render('admin/media_folder/index.html.twig', [
            'folders' => $mediaFolderRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'admin_media_folder_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $folder = new MediaFolder();
        $form = $this->createForm(MediaFolderType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($folder);
            $entityManager->flush();
            $this->addFlash('success', 'Ordner wurde erfolgreich erstellt.');

            return $this->redirectToRoute('admin_mediathek_index');
        }

        return $this->render('admin/media_folder/new.html.twig', [
            'folder' => $folder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_media_folder_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MediaFolder $folder, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MediaFolderType::class, $folder, [
            'exclude_folder_id' => $folder->getId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parent = $folder->getParent();
            if ($parent !== null && ($parent->getId() === $folder->getId() || $this->isUnderFolder($parent, $folder))) {
                $this->addFlash('danger', 'Ungültige Ordnerhierarchie.');

                return $this->redirectToRoute('admin_media_folder_edit', ['id' => $folder->getId()]);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Ordner wurde erfolgreich aktualisiert.');

            return $this->redirectToRoute('admin_mediathek_index');
        }

        return $this->render('admin/media_folder/edit.html.twig', [
            'folder' => $folder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_media_folder_delete', methods: ['POST'])]
    public function delete(Request $request, MediaFolder $folder, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_media_folder' . $folder->getId(), $request->getPayload()->getString('_token'))) {
            if (!$folder->getMediaItems()->isEmpty()) {
                $this->addFlash('danger', 'Ordner enthält noch Medien und kann nicht gelöscht werden.');
            } elseif (!$folder->getChildren()->isEmpty()) {
                $this->addFlash('danger', 'Ordner enthält Unterordner und kann nicht gelöscht werden.');
            } else {
                $entityManager->remove($folder);
                $entityManager->flush();
                $this->addFlash('success', 'Ordner wurde erfolgreich gelöscht.');
            }
        }

        return $this->redirectToRoute('admin_mediathek_index');
    }

    private function isUnderFolder(MediaFolder $node, MediaFolder $potentialAncestor): bool
    {
        $p = $node;
        $guard = 0;
        while ($p !== null && $guard < 100) {
            if ($p->getId() === $potentialAncestor->getId()) {
                return true;
            }
            $p = $p->getParent();
            ++$guard;
        }

        return false;
    }
}
