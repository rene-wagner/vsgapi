<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/page-content')]
class PageContentController extends AbstractController
{
    #[Route('', name: 'admin_page_content', methods: ['GET'])]
    public function index(
        #[Autowire('%page_content.iframe_url%')] string $iframeUrl,
    ): Response {
        return $this->render('admin/page_content/index.html.twig', [
            'iframe_url' => $iframeUrl,
        ]);
    }
}
