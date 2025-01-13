<?php

namespace App\Command;

use App\Repository\PostRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

#[AsCommand(name: 'app:build-search-index')]
class BuildSearchIndexCommand extends Command
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private PostRepository $postRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Connection $connection */
        $connection = $this->doctrine->getConnection();
        $connection->executeQuery('DELETE FROM search');

        foreach ($this->postRepository->getForDbUpdate() as $array) {
            $crawler = new Crawler($array['html']);
            $searchIndex = '';
            $filters = [
                'article .text-body',
                '.comment-body',
                '.post-type-battle .text-body',
            ];
            foreach ($filters as $filter) {
                $result = $crawler->filter($filter);
                if ($result->count() > 0) {
                    $searchIndex .= mb_strtolower($result->text()).PHP_EOL;
                }
            }

            $connection->insert('search', [
                'id' => $array['id'],
                'title' => $array['title'],
                'body' => $searchIndex,
            ]);
        }

        return Command::SUCCESS;
    }
}
