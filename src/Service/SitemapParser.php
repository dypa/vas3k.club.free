<?php

namespace App\Service;

use App\Dto\SitemapLocNode;
use App\Enum\PostType;
use DateTime;
use GuzzleHttp\Client;

use function simplexml_load_string;

final class SitemapParser
{
    private const REGEXP = '.*//vas3k.club/(.*)/(.*)/';

    /**
     * @return SitemapLocNode[]
     */
    public function __invoke(): array
    {
        $content = $this->getSitemap();

        return $this->parseSitemap($content);
    }

    private function getSitemap(): string
    {
        $client = new Client();
        $request = $client->get('https://vas3k.club/sitemap.xml');

        return $request->getBody()->getContents();
    }

    /**
     * @return SitemapLocNode[]
     */
    private function parseSitemap(string $content): array
    {
        $xmlNode = simplexml_load_string($content);
        $urls = [];
        foreach ($xmlNode->children() as $node) {
            $loc = (string) $node->loc;
            $lastmod = (string) $node->lastmod;
            $dto = new SitemapLocNode();
            $dto->location = $loc;
            $dto->lastmod = DateTime::createFromFormat('Y-m-d', $lastmod);
            $dto->type = PostType::from(preg_replace('~'.self::REGEXP.'~', '$1', $loc));
            $dto->clubId = preg_replace('~'.self::REGEXP.'~', '$2', $loc);

            $urls[] = $dto;
        }

        return $urls;
    }
}
