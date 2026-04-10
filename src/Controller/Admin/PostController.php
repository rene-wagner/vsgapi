<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Service\Media\MediaUrlService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/posts')]
class PostController extends AbstractController
{
    #[Route('', name: 'admin_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('admin/post/index.html.twig', [
            'posts' => $postRepository->findBy([], ['updatedAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MediaUrlService $mediaUrlService): Response
    {
        $post = new Post();
        $post->setHits(0);
        $post->setPublished(false);
        $post->setOldPost(false);

        $user = $this->getUser();
        if ($user instanceof User) {
            $post->setAuthor($user);
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Beitrag wurde erfolgreich erstellt.');

            return $this->redirectToRoute('admin_post_index');
        }

        return $this->render('admin/post/new.html.twig', [
            'post' => $post,
            'form' => $form,
            'media_url' => $mediaUrlService,
        ]);
    }

    #[Route('/{id}', name: 'admin_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('admin/post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager, MediaUrlService $mediaUrlService): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Beitrag wurde erfolgreich aktualisiert.');

            return $this->redirectToRoute('admin_post_index');
        }

        return $this->render('admin/post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
            'media_url' => $mediaUrlService,
        ]);
    }

    #[Route('/{id}', name: 'admin_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();

            $this->addFlash('success', 'Beitrag wurde erfolgreich gelöscht.');
        }

        return $this->redirectToRoute('admin_post_index');
    }
}
