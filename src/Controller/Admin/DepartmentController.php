<?php

namespace App\Controller\Admin;

use App\Entity\Department;
use App\Form\DepartmentType;
use App\Repository\DepartmentRepository;
use App\Service\Media\MediaUrlService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/departments')]
class DepartmentController extends AbstractController
{
    #[Route('', name: 'admin_department_index', methods: ['GET'])]
    public function index(DepartmentRepository $departmentRepository): Response
    {
        return $this->render('admin/department/index.html.twig', [
            'departments' => $departmentRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'admin_department_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $department = new Department();
        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($department);
            $entityManager->flush();

            $this->addFlash('success', 'Abteilung wurde erfolgreich erstellt.');

            return $this->redirectToRoute('admin_department_index');
        }

        return $this->render('admin/department/new.html.twig', [
            'department' => $department,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_department_show', methods: ['GET'])]
    public function show(Department $department): Response
    {
        return $this->render('admin/department/show.html.twig', [
            'department' => $department,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_department_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Department $department, EntityManagerInterface $entityManager, MediaUrlService $mediaUrlService): Response
    {
        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Abteilung wurde erfolgreich aktualisiert.');

            return $this->redirectToRoute('admin_department_index');
        }

        return $this->render('admin/department/edit.html.twig', [
            'department' => $department,
            'form' => $form,
            'media_url' => $mediaUrlService,
        ]);
    }

    #[Route('/{id}', name: 'admin_department_delete', methods: ['POST'])]
    public function delete(Request $request, Department $department, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $department->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($department);
            $entityManager->flush();

            $this->addFlash('success', 'Abteilung wurde erfolgreich gelöscht.');
        }

        return $this->redirectToRoute('admin_department_index');
    }
}
