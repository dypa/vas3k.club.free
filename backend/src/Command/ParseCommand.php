<?php

namespace App\Command;

use App\Repository\PostRepository;
use App\Service\PostPageParser;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:parse')]
class ParseCommand extends Command
{
    private const FLUSH_NUMBER = 50;

    public function __construct(
        private ManagerRegistry $doctrine,
        private PostRepository $postRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityManager = $this->doctrine->getManager();

        $i = 0;
        foreach ($this->postRepository->findForUpdateIterator() as $array) {
            $id = $array['id'];
            $pageParser = new PostPageParser();
            $post = $this->postRepository->findOneById($id);
            $pageParser($post);

            ++$i;
            if (0 == $i % 5) {
                echo '+';
            }
            if ($i > self::FLUSH_NUMBER) {
                $entityManager->flush();
                $entityManager->clear();
                gc_collect_cycles();
                gc_mem_caches();
                // TODO memory steel leaks
                $i = 0;
                echo '|' . \PHP_EOL;
                echo round(memory_get_usage() / 1024 / 1024, 3) . \PHP_EOL;
            }
        }

        $entityManager->flush();

        return Command::SUCCESS;
    }
}
