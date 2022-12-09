<?php

namespace App\Controller;

use App\Entity\Post;
use App\Enum\PostType;
use App\Service\PostPageParser;
use App\Service\SitemapParser;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

final class IndexController
{
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
        $postRepository = $doctrine->getRepository(Post::class);

        $urls = $sitemapParser();
        foreach ($urls as $url) {
            if (!in_array($url->type, [PostType::INTRO, PostType::WEEKLY_DIGEST])) {
                $entity = $postRepository->findOneBy(['clubId' => $url->clubId]);
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
        $postRepository = $doctrine->getRepository(Post::class);
        $page = new PostPageParser();

        $post = $postRepository->findOneBy(['id' => $id]);
        if (!$post) {
            throw new NotFoundHttpException();
        }

        $url = $page->getUrl($post);

        $post = $page($post);

        if ($post->deletedAt) {
            $url = '/404';
        } else {
            $post->viewedAt = new DateTime();
        }

        $entityManager->flush();

        return new RedirectResponse($url);
    }

    #[Route('/date/{dateAsString}', methods: ['GET'])]
    public function fetchDate(string $dateAsString, ManagerRegistry $doctrine): RedirectResponse
    {
        $entityManager = $doctrine->getManager();
        $postRepository = $doctrine->getRepository(Post::class);
        $page = new PostPageParser();

        $posts = $postRepository->findByDate($dateAsString);
        foreach ($posts as $post) {
            $page($post);
            sleep(1);
        }
        $entityManager->flush();

        return new RedirectResponse('/');
    }
}
