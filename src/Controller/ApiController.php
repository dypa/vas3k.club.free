<?php

namespace App\Controller;

use App\Entity\Post;
use App\Enum\VoteType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
final class ApiController
{
    #[Route('/progress', methods: ['GET'])]
    public function progress(ManagerRegistry $doctrine): JsonResponse
    {
        $urlRepository = $doctrine->getRepository(Post::class);
        $progress = $urlRepository->countProgress();

        return new JsonResponse(['total' => $progress[0], 'viewed' => $progress[1]]);
    }

    #[Route('/new', methods: ['GET'])]
    public function new(ManagerRegistry $doctrine): JsonResponse
    {
        $postRepository = $doctrine->getRepository(Post::class);
        $posts = $postRepository->findNew();

        return new JsonResponse($posts);
    }

    #[Route('/best', methods: ['GET'])]
    public function best(ManagerRegistry $doctrine): JsonResponse
    {
        $postRepository = $doctrine->getRepository(Post::class);
        $posts = $postRepository->findBest();

        return new JsonResponse($posts);
    }

    #[Route('/past', methods: ['GET'])]
    public function past(ManagerRegistry $doctrine): JsonResponse
    {
        $postRepository = $doctrine->getRepository(Post::class);
        $posts = $postRepository->findPast();

        return new JsonResponse($posts);
    }

    #[Route('/vote/{typeId}/{postId}', methods: ['GET'])]
    public function vote(int $typeId, int $postId, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $postRepository = $doctrine->getRepository(Post::class);

        $type = VoteType::from($typeId);
        $post = $postRepository->findOneBy(['id' => $postId]);

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
        $postRepository = $doctrine->getRepository(Post::class);
        $posts = $postRepository->search($word);

        return new JsonResponse($posts);
    }
}
