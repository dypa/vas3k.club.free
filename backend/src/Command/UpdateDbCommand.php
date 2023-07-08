<?php

namespace App\Command;

use App\Repository\PostRepository;
use App\Service\PostPageParser;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update-db')]
class UpdateDbCommand extends Command
{
    public function __construct(private PostPageParser $pageParser, private ManagerRegistry $doctrine, private readonly PostRepository $postRepository)
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityManager = $this->doctrine->getManager();

        // HACK
        $this->doctrine->getConnection()->getConfiguration()->getSQLLogger(null);

        $posts = $this->postRepository->getForDbUpdate();

        $i = 1;
        foreach ($posts as $postId) {
            $post = $this->postRepository->findOneBy(['id' => $postId]);

            $this->pageParser->crawlAndSave($post);
            usleep(5 * 500);
            ++$i;

            if (0 == $i % 50) {
                $entityManager->flush();
                $entityManager->clear();
                gc_enable();
                echo $i.' --- '.round(memory_get_usage() / 1024 / 1024, 0).' --- '.round(memory_get_peak_usage() / 1024 / 1024, 0);
                echo PHP_EOL;
            }
        }
        $entityManager->flush();

        return Command::SUCCESS;
    }
}
