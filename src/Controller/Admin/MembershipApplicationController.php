<?php

namespace App\Controller\Admin;

use App\Entity\MembershipApplication;
use App\Repository\MembershipApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/membership-applications')]
final class MembershipApplicationController extends AbstractController
{
    #[Route('', name: 'admin_membership_application_index', methods: ['GET'])]
    public function index(MembershipApplicationRepository $membershipApplicationRepository): Response
    {
        return $this->render('admin/membership_application/index.html.twig', [
            'membership_applications' => $membershipApplicationRepository->findAllOrderedByCreatedAtDesc(),
        ]);
    }

    #[Route('/{id}', name: 'admin_membership_application_show', methods: ['GET'])]
    public function show(MembershipApplication $membershipApplication): Response
    {
        return $this->render('admin/membership_application/show.html.twig', [
            'membership_application' => $membershipApplication,
        ]);
    }
}
