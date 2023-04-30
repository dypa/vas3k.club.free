<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Service\PostPageParser;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

final class IndexController
{
    public function __construct(private readonly PostRepository $postRepository)
    {
    }

    #[Route('/', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): NotFoundHttpException
    {
        return new NotFoundHttpException();
    }

    #[Route('/go/{id}', methods: ['GET'])]
    public function go(int $id, ManagerRegistry $doctrine): RedirectResponse
    {
        $entityManager = $doctrine->getManager();
        $page = new PostPageParser();

        $post = $this->postRepository->find($id);
        if (!$post) {
            throw new NotFoundHttpException();
        }

        $url = $page->getUrl($post);

        if ($post->viewedAt) {
            $url .= '?comment_order=-created_at#comments';
        }

        $post = $page($post);

        if ($post->deletedAt) {
            $url = '/404';
        } else {
            $post->viewedAt = new \DateTime();
        }

        $entityManager->flush();

        return new RedirectResponse($url);
    }
}
