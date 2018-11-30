<?php

namespace Chilicon\Pimcore\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbDumpCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('chilicon:db:dump')
            ->setDescription('Creates database dump of the current Pimcore instance.')
            ->setHelp(
                'Creates database dump of the current Pimcore instance.'
                . ' and saves it to predefined directory (like "chilicon-it/<HOST_NAME>/database/<DB_NAME>-<YYMMDD>-<hhmmss>.sql").'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo "DB dump command.\n";
    }
}
