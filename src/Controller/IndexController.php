<?php

namespace App\Controller;

use App\Entity\Post;
use App\Enum\PostType;
use App\Repository\PostRepository;
use App\Service\PostPageParser;
use App\Service\SitemapParser;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

final class IndexController
{
    public function __construct(private readonly PostRepository $postRepository)
    {
    }

    #[Route('/', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): RedirectResponse
    {
        return new RedirectResponse('index.html');
    }

    #[Route('/scrape-sitemap', methods: ['GET'])]
    public function scrapeSitemap(Request $request, ManagerRegistry $doctrine): RedirectResponse
    {
        $sitemapParser = new SitemapParser();
        $entityManager = $doctrine->getManager();

        $urls = $sitemapParser();
        foreach ($urls as $url) {
            if (!in_array($url->type, [PostType::INTRO, PostType::WEEKLY_DIGEST])) {
                $entity = $this->postRepository->findOneBy(['clubId' => $url->clubId]);
                if (!$entity) {
                    $entity = new Post();
                }
                $entity->updatedAt = $url->lastmod;
                $entity->clubId = $url->clubId;
                $entity->postType = $url->type;

                $entityManager->persist($entity);
            }
        }
        $entityManager->flush();

        return new RedirectResponse('/');
    }

    #[Route('/go/{id}', methods: ['GET'])]
    public function go(int $id, ManagerRegistry $doctrine): RedirectResponse
    {
        $entityManager = $doctrine->getManager();
        $page = new PostPageParser();

        $post = $this->postRepository->findOneBy(['id' => $id]);
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

    #[Route('/date/{dateAsString}', methods: ['GET'])]
    public function fetchDate(string $dateAsString, ManagerRegistry $doctrine): RedirectResponse
    {
        $entityManager = $doctrine->getManager();
        $page = new PostPageParser();

        $posts = $this->postRepository->findByDate($dateAsString);
        foreach ($posts as $post) {
            $page($post);
            sleep(1);
        }
        $entityManager->flush();

        return new RedirectResponse('/');
    }
}
