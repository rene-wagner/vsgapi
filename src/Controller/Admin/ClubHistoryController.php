<?php

namespace App\Controller\Admin;

use App\Form\ClubHistoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/verein/geschichte')]
class ClubHistoryController extends AbstractController
{
    #[Route('', name: 'admin_club_history', methods: ['GET'])]
    public function index(): Response
    {
        $form = $this->createForm(ClubHistoryType::class, [
            'foundingDate' => null,
        ]);

        return $this->render('admin/club_history/index.html.twig', [
            'form' => $form,
        ]);
    }
}
