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
        // https://www.sqlite.org/fts5.html#the_delete_all_command
        $connection->executeQuery("INSERT INTO search(search) VALUES('delete-all')");

        foreach ($this->postRepository->findForBuildSearchIndexIterator() as $array) {
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
                    $searchIndex .= mb_strtolower($result->text()) . PHP_EOL;
                }
            }

            if (!is_numeric($array['id'])) {
                continue;
            }
            $connection->insert('search', [
                'ROWID' => $array['id'],
                'title' => $array['title'],
                'body' => $searchIndex,
            ]);
        }

        $this->vacuum();

        return Command::SUCCESS;
    }

    private function vacuum(): void
    {
        $connection = $this->doctrine->getConnection();
        $connection->executeQuery('VACUUM');
    }
}
