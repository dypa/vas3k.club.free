<?php

namespace App\Service;

use App\Entity\Post;
use App\Enum\PostType;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use LogicException;
use Symfony\Component\DomCrawler\Crawler;

final class PostPageParser
{
    public function __invoke(Post $post)
    {
        $url = $this->getUrl($post);

        if (!$post->title) {
            $client = new Client([
                RequestOptions::HTTP_ERRORS => false,
            ]);
            $request = $client->get($url);

            if (404 == $request->getStatusCode()) {
                $post->deletedAt = new DateTime();

                return $post;
            }

            if (200 != $request->getStatusCode()) {
                throw new LogicException();
            }

            $content = $request->getBody()->getContents();

            $crawler = new Crawler($content);

            if (true == $this->isClosed($crawler)) {
                $post->deletedAt = new DateTime();

                return $post;
            }

            if (PostType::BATTLE == $post->postType) {
                list($title, $date) = $this->parseBattleNodes($crawler);
            } else {
                list($title, $date) = $this->parsePageNodes($crawler);
            }

            $date = $this->replaceDate($date);

            $post->createdAt = DateTime::createFromFormat('d m Y', $date);

            // str_replace('\xc2\xa0', ' ', $title)
            $title = str_replace(' ', ' ', $title);
            $title = str_replace(' Публичный пост', '', $title);

            $post->title = $title;
        }

        return $post;
    }

    private function isClosed(Crawler $crawler)
    {
        return $crawler->filter('.access-denied')->count() > 0;
    }

    public function getUrl(Post $post): string
    {
        return 'https://vas3k.club/'.$post->postType->value.'/'.$post->clubId.'/';
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
}
