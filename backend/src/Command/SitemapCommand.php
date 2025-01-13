<?php

namespace App\Command;

use App\Service\SitemapParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:sitemap')]
class SitemapCommand extends Command
{
    public function __construct(
        private readonly SitemapParser $sitemapParser,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->sitemapParser->__invoke();

        return Command::SUCCESS;
    }
}
