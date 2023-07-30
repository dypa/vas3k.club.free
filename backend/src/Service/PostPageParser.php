<?php

namespace App\Service;

use App\Entity\Post;
use App\Enum\PostType;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\DomCrawler\Crawler;

final class PostPageParser
{
    public function __invoke(Post $post)
    {
        if ($post->like || !$post->title) {
            $this->crawlAndSave($post);
        }

        return $post;
    }

    public function crawlAndSave(Post $post): Post
    {
        $html = $this->crawl($post);
        $crawler = new Crawler($html);

        if (true == $this->isClosed($crawler)) {
            $post->deletedAt = new \DateTime();

            return $post;
        }

        if (PostType::BATTLE == $post->postType) {
            list($title, $date) = $this->parseBattleNodes($crawler);
        } else {
            list($title, $date) = $this->parsePageNodes($crawler);
        }

        $date = $this->replaceDate($date);
        $title = $this->prepareTitle($title);

        $post->createdAt = \DateTime::createFromFormat('d m Y', $date);
        $post->title = $title;
        $post->html = $html;

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

    private function crawl(Post $post): string
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
