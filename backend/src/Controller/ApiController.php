<?php

namespace App\Controller;

use App\Entity\Post;
use App\Enum\PostType;
use App\Enum\VoteType;
use App\Repository\PostRepository;
use App\Service\SitemapParser;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
final class ApiController
{
    public function __construct(private readonly PostRepository $postRepository)
    {
    }

    #[Route('/progress', methods: ['GET'])]
    public function progress(ManagerRegistry $doctrine): JsonResponse
    {
        $progress = $this->postRepository->countProgress();

        return new JsonResponse(['total' => $progress[0], 'viewed' => $progress[1], 'updated' => $progress[2]]);
    }

    #[Route('/filter/{type}/{page}', methods: ['GET'], requirements: ['type' => '(new|updated|best|done|favorite)'], defaults: ['page' => 0])]
    public function filter(string $type, string $page, ManagerRegistry $doctrine): JsonResponse
    {
        $paginator = $this->postRepository->filter($type, $page); // {'find' . $type}($page);

        $paginator->setUseOutputWalkers(false);

        return new JsonResponse([
            'total' => ceil(count($paginator) / $this->postRepository->getPostsPeerPage()),
            'data' => $paginator->getQuery()->getResult(),
        ]);
    }

    #[Route('/vote/{typeId}/{postId}', methods: ['GET'])]
    public function vote(int $typeId, int $postId, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $type = VoteType::from($typeId);
        $post = $this->postRepository->findOneBy(['id' => $postId]);

        if (!$post) {
            throw new NotFoundHttpException();
        }

        $post->like = (VoteType::UP == $type);

        $entityManager->flush();

        return new JsonResponse('ok');
    }

    #[Route('/search/{word}', methods: ['GET'])]
    public function search(ManagerRegistry $doctrine, string $word): JsonResponse
    {
        $posts = $this->postRepository->search($word);

        return new JsonResponse($posts);
    }

    #[Route('/scrape', methods: ['GET'])]
    public function scrapeSitemap(ManagerRegistry $doctrine): JsonResponse
    {
        $sitemapParser = new SitemapParser();
        $entityManager = $doctrine->getManager();

        $urls = $sitemapParser();
        foreach ($urls as $url) {
            if (!in_array($url->type, [PostType::INTRO, PostType::WEEKLY_DIGEST])) {
                $entity = $this->postRepository->findOneBy(['clubId' => $url->clubId]);

                if (!$entity) {
                    $entity = new Post();
                    $entity->clubId = $url->clubId;
                    $entity->postType = $url->type;
                    $entityManager->persist($entity);
                }

                $entity->updatedAt = $url->lastmod;

                if (mt_rand(1, 100) >= 50) {
                    $entityManager->flush();
                    $entityManager->clear();
                    gc_enable();
                }
            }
        }
        $entityManager->flush();

        return new JsonResponse('ok');
    }

    #[Route('/mark-all-as-read', methods: ['GET'])]
    public function markAllAsRead(ManagerRegistry $doctrine): JsonResponse
    {
        $this->postRepository->markAllAsRead();

        return new JsonResponse('ok');
    }
}
