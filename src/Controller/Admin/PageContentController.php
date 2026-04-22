<?php

namespace App\Controller\Admin;

use App\Service\Embed\PageContentEmbedUrlBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/admin/page-content')]
class PageContentController extends AbstractController
{
    #[Route('', name: 'admin_page_content', methods: ['GET'])]
    public function index(PageContentEmbedUrlBuilder $embedUrlBuilder): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('admin/page_content/index.html.twig', [
            'iframe_url' => $embedUrlBuilder->build($user),
        ]);
    }
}
