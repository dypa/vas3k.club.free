<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Service\PostPageParser;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

final class IndexController
{
    public function __construct(private readonly PostRepository $postRepository)
    {
    }

    #[Route('/', methods: ['GET'])]
    public function index(): NotFoundHttpException
    {
        return new NotFoundHttpException();
    }

    #[Route('/go/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function go(string $id, ManagerRegistry $doctrine): RedirectResponse
    {
        $entityManager = $doctrine->getManager();
        $pageParser = new PostPageParser();

        /** @var Post $post */
        $post = $this->postRepository->find($id);
        if (!$post) {
            throw new NotFoundHttpException();
        }

        if ($post->viewedAt < $post->lastModified || $post->viewedAt < $post->lastModified) {
            $post = $pageParser($post);
        }

        $url = '/html/'.$id;

        if ($post->viewedAt) {
            $url .= '?comment_order=-created_at#comments';
        }

        if ($post->deletedAt) {
            $url = '/404';
        } else {
            $post->viewedAt = new \DateTime();
        }

        $entityManager->flush();

        return new RedirectResponse($url);
    }

    #[Route('/html/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function html(string $id, ManagerRegistry $doctrine): Response
    {
        $post = $this->postRepository->find($id);
        if (!$post) {
            throw new NotFoundHttpException();
        }

        return new Response($post->html);
    }
}
