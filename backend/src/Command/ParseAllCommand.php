<?php

namespace App\Command;

use App\Repository\PostRepository;
use App\Service\PostPageParser;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:parse-all')]
class ParseAllCommand extends Command
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private PostRepository $postRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        exit;
        $entityManager = $this->doctrine->getManager();
        $pageParser = new PostPageParser();

        $i = 0;
        foreach ($this->postRepository->findAllIterator() as $array) {
            $pageParser($this->postRepository->findOneById($array['id']));
            ++$i;
            if (0 == $i % 5) {
                echo '.';
            }
            if ($i > 50) {
                $entityManager->flush();
                $entityManager->clear();
                $i = 0;
                echo '|';
            }
        }

        $entityManager->flush();

        return Command::SUCCESS;
    }
}
