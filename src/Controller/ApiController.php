<?php

namespace App\Controller;

use App\Enum\VoteType;
use App\Repository\PostRepository;
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

        return new JsonResponse(['total' => $progress[0], 'viewed' => $progress[1]]);
    }

    #[Route('/new', methods: ['GET'])]
    public function new(ManagerRegistry $doctrine): JsonResponse
    {
        $posts = $this->postRepository->findNew();

        return new JsonResponse($posts);
    }

    #[Route('/favorite', methods: ['GET'])]
    public function favorite(ManagerRegistry $doctrine): JsonResponse
    {
        $posts = $this->postRepository->findFavorite();

        return new JsonResponse($posts);
    }

    #[Route('/done', methods: ['GET'])]
    public function done(ManagerRegistry $doctrine): JsonResponse
    {
        $posts = $this->postRepository->findDone();

        return new JsonResponse($posts);
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

    #[Route('/best', methods: ['GET'])]
    public function best(ManagerRegistry $doctrine): JsonResponse
    {
        $posts = $this->postRepository->findBest();

        return new JsonResponse($posts);
    }

    #[Route('/updated', methods: ['GET'])]
    public function updated(ManagerRegistry $doctrine): JsonResponse
    {
        $posts = $this->postRepository->findUpdated();

        return new JsonResponse($posts);
    }
}
