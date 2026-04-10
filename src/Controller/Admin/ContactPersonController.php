<?php

namespace App\Controller\Admin;

use App\Entity\ContactPerson;
use App\Form\ContactPersonType;
use App\Repository\ContactPersonRepository;
use App\Service\Media\MediaUrlService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/contact-people')]
class ContactPersonController extends AbstractController
{
    #[Route('', name: 'admin_contact_person_index', methods: ['GET'])]
    public function index(ContactPersonRepository $contactPersonRepository): Response
    {
        return $this->render('admin/contact_person/index.html.twig', [
            'contactPeople' => $contactPersonRepository->findBy([], ['lastName' => 'ASC', 'firstName' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'admin_contact_person_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MediaUrlService $mediaUrlService): Response
    {
        $contactPerson = new ContactPerson();
        $form = $this->createForm(ContactPersonType::class, $contactPerson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contactPerson);
            $entityManager->flush();

            $this->addFlash('success', 'Kontaktperson wurde erfolgreich erstellt.');

            return $this->redirectToRoute('admin_contact_person_index');
        }

        return $this->render('admin/contact_person/new.html.twig', [
            'contact_person' => $contactPerson,
            'form' => $form,
            'media_url' => $mediaUrlService,
        ]);
    }

    #[Route('/{id}', name: 'admin_contact_person_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(ContactPerson $contactPerson): Response
    {
        return $this->render('admin/contact_person/show.html.twig', [
            'contact_person' => $contactPerson,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_contact_person_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, ContactPerson $contactPerson, EntityManagerInterface $entityManager, MediaUrlService $mediaUrlService): Response
    {
        $form = $this->createForm(ContactPersonType::class, $contactPerson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Kontaktperson wurde erfolgreich aktualisiert.');

            return $this->redirectToRoute('admin_contact_person_index');
        }

        return $this->render('admin/contact_person/edit.html.twig', [
            'contact_person' => $contactPerson,
            'form' => $form,
            'media_url' => $mediaUrlService,
        ]);
    }

    #[Route('/{id}', name: 'admin_contact_person_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, ContactPerson $contactPerson, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $contactPerson->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($contactPerson);
            $entityManager->flush();

            $this->addFlash('success', 'Kontaktperson wurde erfolgreich gelöscht.');
        }

        return $this->redirectToRoute('admin_contact_person_index');
    }
}
