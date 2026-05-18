<?php

namespace App\Controller\Admin;

use App\Entity\ClubHistory;
use App\Form\ClubHistoryType;
use App\Repository\ClubHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/club-history')]
class ClubHistoryController extends AbstractController
{
    #[Route('', name: 'admin_club_history', methods: ['GET', 'POST'])]
    public function edit(Request $request, ClubHistoryRepository $clubHistoryRepository, EntityManagerInterface $entityManager): Response
    {
        $clubHistory = $clubHistoryRepository->findSingleton() ?? new ClubHistory();

        $form = $this->createForm(ClubHistoryType::class, $clubHistory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($clubHistory->getId() === null) {
                $entityManager->persist($clubHistory);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Die Vereinsdaten wurden erfolgreich gespeichert.');

            return $this->redirectToRoute('admin_club_history');
        }

        return $this->render('admin/club_history/edit.html.twig', [
            'club_history' => $clubHistory,
            'form' => $form,
        ]);
    }
}
