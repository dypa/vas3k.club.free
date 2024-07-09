<?php

namespace App\Command;

use App\Repository\PostRepository;
use App\Service\PostPageParser;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:build-search-index')]
class BuildSearchIndexCommand extends Command
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private PostRepository $postRepository,
    ) {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityManager = $this->doctrine->getManager();
        $pageParser = new PostPageParser();

        $i = 0;
        foreach ($this->postRepository->findAllIterator() as $array) {
            $post = $this->postRepository->findOneById($array['id']);
            $post = $pageParser->crawlAndSave($post);
            ++$i;
            if ($i > 25) {
                $entityManager->flush();
                $i = 0;
                echo '.';
            }
        }

        $entityManager->flush();

        return Command::SUCCESS;
    }
}
