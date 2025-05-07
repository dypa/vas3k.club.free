<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Service\PostPageParser;
use Doctrine\Persistence\ManagerRegistry;
use GuzzleHttp\Exception\ConnectException;
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

        $url = '/html/' . $id;
        if ($post->viewedAt && $post->html) {
            $url .= '#comments';
        }

        if ($post->viewedAt < $post->lastModified || $post->viewedAt < $post->lastModified) {
            try {
                $post = $pageParser($post);

                if ($post->deletedAt && empty($post->title)) {
                    $url = '/404';
                } else {
                    $post->viewedAt = new \DateTime();
                }
            } catch (ConnectException $e) {
            }
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

        $html = $post->html;

        $uri = 'https://vas3k.club/' . $post->postType->value . '/' . $post->id . '/';
        $htmlPatch = "<a href=\"$uri\" style=\"text-decoration:none\">🌐</a>";
        $cssPatch = <<<CSS
            <style>
            :root {--serif-font: var(--sans-font) !important;}
            .fa-eye::before{
                content: "👁" !important
            }
            </style>
            CSS;
        $icoPatch = <<<ICO
                <link
                href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>💩</text></svg>"
                rel="icon">
            ICO;

        $html = str_replace('<body>', '<body>' . $htmlPatch, $html);
        $html = str_replace('<body>', '<body>' . $cssPatch, $html);
        $html = str_replace('</title>', '</title>' . $icoPatch, $html);

        return new Response($html);
    }
}
