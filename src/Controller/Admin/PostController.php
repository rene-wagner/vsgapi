<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Service\Ai\OpenRouterPostRewriteService;
use App\Service\Media\MediaUrlService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/posts')]
class PostController extends AbstractController
{
    #[Route('', name: 'admin_post_index', methods: ['GET'])]
    public function index(
        PostRepository $postRepository,
        #[MapQueryParameter] int $page = 1,
    ): Response {
        $limit = 30;

        if ($page < 1) {
            $page = 1;
        }

        [$posts, $total] = $postRepository->findPaginatedOrderByCreatedAtDesc($page, $limit);

        return $this->render('admin/post/index.html.twig', [
            'posts' => $posts,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
        ]);
    }

    #[Route('/new', name: 'admin_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
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

    #[Route('/rewrite', name: 'admin_post_rewrite', methods: ['POST'])]
    public function rewrite(Request $request, OpenRouterPostRewriteService $openRouterPostRewriteService): JsonResponse
    {
        try {
            $payload = $request->toArray();
        } catch (\JsonException) {
            return $this->json([
                'message' => 'Die Anfrage ist ungültig.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $token = $payload['_token'] ?? null;
        $content = $payload['content'] ?? null;

        if (!is_string($token) || !$this->isCsrfTokenValid('post_rewrite', $token)) {
            return $this->json([
                'message' => 'Der Sicherheitstoken ist ungültig.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!is_string($content)) {
            return $this->json([
                'message' => 'Es wurde kein Text übermittelt.',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $rewrittenContent = $openRouterPostRewriteService->rewrite($content);
        } catch (\InvalidArgumentException $exception) {
            return $this->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $exception) {
            return $this->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);
        }

        return $this->json([
            'content' => $rewrittenContent,
            'systemPrompt' => $openRouterPostRewriteService->getSystemPrompt(),
            'model' => $openRouterPostRewriteService->getModel(),
            'modelUrl' => $openRouterPostRewriteService->getModelUrl(),
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
