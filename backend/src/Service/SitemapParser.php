<?php

namespace App\Service;

use App\Dto\SitemapLocNode;
use App\Entity\Post;
use App\Enum\PostType;
use App\Repository\PostRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use GuzzleHttp\Client;

final class SitemapParser
{
    private const REGEXP = '.*//vas3k.club/(.*)/(.*)/';
    private const SITEMAP_XML = 'https://vas3k.club/sitemap.xml';

    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly PostRepository $postRepository,
        private /* readonly */ Connection $connection,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    // TODO refactor SQL logic to repository
    public function __invoke(): void
    {
        $content = $this->getSitemap();
        $urls = $this->parseSitemap($content);

        $this->connection->executeQuery('CREATE TEMPORARY TABLE sitemap (lastmod, clubId, postType)');
        foreach ($urls as $url) {
            if (!in_array($url->type, [PostType::INTRO, PostType::WEEKLY_DIGEST])) {
                $this->connection->executeQuery("
                INSERT INTO temp.sitemap (lastmod, clubId, postType) 
                VALUES 
                (
                    '{$url->lastmod->format('Y-m-d')}', 
                    '{$url->clubId}', 
                    '{$url->type->value}'
                )
                ");
            }
        }

        $this->new($urls);
        $this->update($urls);
        $this->cleanUp();

        $this->entityManager->flush();

        // $this->vacuum();
    }

    private function getSitemap(): string
    {
        $client = new Client();
        $request = $client->get(self::SITEMAP_XML);

        return $request->getBody()->getContents();
    }

    /**
     * @return SitemapLocNode[]
     */
    private function parseSitemap(string $content): array
    {
        $xmlNode = \simplexml_load_string($content);
        $urls = [];
        foreach ($xmlNode->children() as $node) {
            $loc = (string) $node->loc;
            $lastmod = (string) $node->lastmod;
            $dto = new SitemapLocNode();
            $dto->lastmod = \DateTime::createFromFormat('Y-m-d', $lastmod);
            $dto->type = PostType::from(preg_replace('~'.self::REGEXP.'~', '$1', $loc));
            $dto->clubId = preg_replace('~'.self::REGEXP.'~', '$2', $loc);

            $urls[$dto->clubId] = $dto;
        }

        return $urls;
    }

    private function new(array $urls)
    {
        $rows = $this->connection->fetchAllAssociative('
        SELECT 
            s.clubId
        FROM temp.sitemap AS s
            LEFT JOIN post AS p ON s.clubId = p.id
        WHERE 
            p.id IS NULL
        ');

        foreach ($rows as $record) {
            $clubId = $record['clubId'];

            $url = $urls[$clubId];

            $entity = new Post();
            $entity->id = $url->clubId;
            $entity->postType = $url->type;

            $entity->lastModified = $url->lastmod;
            $this->entityManager->persist($entity);
        }
    }

    private function update(array $urls)
    {
        $this->connection = $this->doctrine->getConnection();

        $rows = $this->connection->fetchAllAssociative('
        SELECT 
            s.clubId
        FROM temp.sitemap AS s
            LEFT JOIN post AS p ON s.clubId = p.id
        WHERE 
            p.last_modified <> s.lastmod
        ');

        foreach ($rows as $record) {
            $clubId = $record['clubId'];

            $url = $urls[$clubId];

            $entity = $this->postRepository->findOneBy(['id' => $url->clubId]);
            $entity->lastModified = $url->lastmod;
            $entity->deletedAt = null;
        }
    }

    private function cleanUp()
    {
        $rows = $this->connection->fetchAllAssociative('
        SELECT 
            p.id
        FROM temp.sitemap AS s
            RIGHT JOIN post AS p ON s.clubId = p.id
        WHERE 
            s.clubId IS NULL and p.deleted_at IS NULL
        ');

        foreach ($rows as $record) {
            $entity = $this->postRepository->findOneBy(['id' => $record['id']]);
            $entity->deletedAt = new \DateTime();
        }
    }

    /**
     * TODO disabled because has /html/:id route.
     * TODO remove search index.
     */
    private function vacuum(): void
    {
        $this->connection->executeQuery('
        UPDATE post SET html = NULL
        WHERE 
            deleted_at IS NULL
            AND 
            "like" = 0
            AND 
            viewed_at < DATE(\'now\', \'-7 days\') 
        ');
        $this->connection->executeQuery('
        UPDATE post SET html = NULL
        WHERE 
            deleted_at IS NOT NULL
            AND 
            "like" = 0
            AND 
            viewed_at < DATE(\'now\', \'-30 days\') 
        ');

        $this->connection->executeQuery('VACUUM');
    }
}
