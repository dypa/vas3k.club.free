<?php

namespace App\Service;

use App\Entity\Post;
use App\Enum\PostType;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\DomCrawler\Crawler;
use voku\helper\HtmlMin;

final class PostPageParser
{
    public function __invoke(Post $post)
    {
        $html = $this->getHtml($post);
        $crawler = new Crawler($html);

        $removeSelectors = [
            'nav.menu',
            '#footer',
            '.post-join',
        ];

        foreach ($removeSelectors as $selector) {
            $result = $crawler->filter($selector);
            if ($result->count() > 0) {
                $result->each(function (Crawler $crawler) {
                    foreach ($crawler as $node) {
                        $node->parentNode->removeChild($node);
                    }
                });
            }
        }

        if (true == $this->isClosed($crawler)) {
            $post->deletedAt = new \DateTime();

            return $post;
        }

        if (PostType::BATTLE == $post->postType) {
            [$title, $date] = $this->parseBattleNodes($crawler);
        } else {
            [$title, $date] = $this->parsePageNodes($crawler);
        }

        $date = $this->replaceDate($date);
        $title = $this->prepareTitle($title);

        $post->createdAt = \DateTime::createFromFormat('d m Y', $date);
        $post->title = $title;

        $minifier = new HtmlMin();
        $html = str_replace('href="/', 'href="https://vas3k.club/', $crawler->html());
        $errorLevel = error_reporting(0);
        $post->html = $minifier->minify($html);
        error_reporting($errorLevel);

        return $post;
    }

    private function isClosed(Crawler $crawler)
    {
        $isAccessDeny = $crawler->filter('.access-denied')->count() > 0;
        $noBody = $crawler->filter('body')->count() < 1;

        return $isAccessDeny || $noBody;
    }

    public function getUrl(Post $post): string
    {
        return 'https://vas3k.club/'.$post->postType->value.'/'.$post->id.'/';
    }

    private function replaceDate(string $date): string
    {
        $date = str_replace([
            'января',
            'февраля',
            'марта',
            'апреля',
            'мая',
            'июня',
            'июля',
            'августа',
            'сентября',
            'октября',
            'ноября',
            'декабря',
        ], range(1, 12), $date);

        return $date;
    }

    private function parseBattleNodes(Crawler $crawler): array
    {
        $text = $crawler->filter('.battle-title')->text();
        $date = $crawler->filter('.battle-title-main .post-actions-line .post-actions-line-item')->first()->text();

        return [$text, $date];
    }

    private function parsePageNodes(Crawler $crawler): array
    {
        $text = $crawler->filter('.post-title')->text();
        $date = $crawler->filter('article .post-actions-line .post-actions-line-item')->first()->text();

        return [$text, $date];
    }

    private function prepareTitle(mixed $title): string|array
    {
        // str_replace('\xc2\xa0', ' ', $title)
        $title = str_replace(' ', ' ', $title);
        $title = str_replace(' Публичный пост', '', $title);

        return $title;
    }

    private function getHtml(Post $post): string
    {
        $client = new Client([
            RequestOptions::HTTP_ERRORS => false,
        ]);
        $request = $client->get($this->getUrl($post));

        if (404 == $request->getStatusCode()) {
            return '';
        }

        if (200 != $request->getStatusCode()) {
            throw new \LogicException();
        }

        return $request->getBody()->getContents();
    }
}
