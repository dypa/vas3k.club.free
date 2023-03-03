<?php

namespace App\Command;

use App\Entity\Post;
use App\Service\PostPageParser;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update-db')]
class UpdateDbCommand extends Command
{
    public function __construct(private PostPageParser $pageParser, private ManagerRegistry $doctrine)
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityManager = $this->doctrine->getManager();
        $postRepository = $this->doctrine->getRepository(Post::class);
        $posts = $postRepository->getForDbUpdate();
        $i = 1;
        foreach ($posts as $post) {
            $this->pageParser->crawlAndSave($post);
            sleep(1);
            ++$i;

            if (mt_rand(1, 100) >= 80) {
                $entityManager->flush();
                echo $i.PHP_EOL;
            }
        }
        $entityManager->flush();

        return Command::SUCCESS;
    }
}
