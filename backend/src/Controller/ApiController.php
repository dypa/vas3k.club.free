<?php

namespace App\Controller;

use App\Enum\VoteType;
use App\Repository\PostRepository;
use App\Service\PostPageParser;
use App\Service\SitemapParser;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
final class ApiController
{
    public function __construct(
        private readonly PostRepository $postRepository,
    ) {
    }

    #[Route('/progress', methods: ['GET'])]
    public function progress(): JsonResponse
    {
        $progress = $this->postRepository->countProgress();

        return new JsonResponse([
            'total' => $progress[0],
            'viewed' => $progress[1],
            'updated' => $progress[2],
            'liked' => $progress[3],
        ]);
    }

    #[Route('/filter/{type}/{page}', methods: ['GET'], requirements: ['type' => '(new|updated|done|favorite)', 'page' => '\d+'], defaults: ['page' => 0])]
    public function filter(string $type, string $page): JsonResponse
    {
        $paginator = $this->postRepository->filter($type, $page);

        return $this->createResponseFromPaginator($paginator);
    }

    #[Route('/filter/deleted/{page}', methods: ['GET'], defaults: ['page' => 0], requirements: ['page' => '\d+'])]
    public function deleted(string $page): JsonResponse
    {
        $paginator = $this->postRepository->deleted($page);

        return $this->createResponseFromPaginator($paginator);
    }

    private function createResponseFromPaginator($paginator): JsonResponse
    {
        $paginator->setUseOutputWalkers(false);

        return new JsonResponse([
            'total' => ceil(count($paginator) / $this->postRepository->getPostsPeerPage()),
            'data' => $paginator->getQuery()->getResult(),
        ]);
    }

    #[Route('/vote/{typeId}/{postId}', methods: ['GET'], requirements: ['typeId' => '\d+', 'postId' => '\d+'])]
    public function vote(int $typeId, int $postId, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $type = VoteType::from($typeId);
        $post = $this->postRepository->findOneBy(['id' => $postId]);

        if (!$post) {
            throw new NotFoundHttpException();
        }

        $post->like = (VoteType::UP == $type);

        if ($post->like) {
            $pageParser = new PostPageParser();
            $post = $pageParser($post);
        }

        $entityManager->flush();

        return new JsonResponse(true);
    }

    #[Route('/search/{word}', methods: ['GET'])]
    public function search(string $word): JsonResponse
    {
        $posts = $this->postRepository->search($word);

        return new JsonResponse($posts);
    }

    #[Route('/scrape', methods: ['GET'])]
    public function scrapeSitemap(ManagerRegistry $doctrine): JsonResponse
    {
        $sitemapParser = new SitemapParser($doctrine, $this->postRepository, $doctrine->getConnection(), $doctrine->getManager());
        $sitemapParser();

        return new JsonResponse(true);
    }

    #[Route('/mark-all-as-read', methods: ['GET'])]
    public function markAllAsRead(): JsonResponse
    {
        $this->postRepository->markAllAsRead();

        return new JsonResponse(true);
    }
}
