<?php

namespace Chilicon\Pimcore\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Clears Pimcore cache - executes 'cache:clear' command after permissions of 'var' directory changed.
 *
 * @author Yaroslav Chupikov
 * @copyright (C) 2018 Chilicon IT
 * @version 1.0.0
 */
class ClearCacheCommand extends AbstractCommand
{
    const COMMAND_NAME = 'chilicon:cache:clear';
    const VERSION = '1.0.0';

    public function getVersion(): string
    {
        return self::VERSION;
    }

    protected function configure()
    {
        $this
        ->setName(self::COMMAND_NAME)
        ->setDescription('Clears Pimcore cache without errors.')
        ->setHelp(
            'Executes "cache:clear" command after permissions of "var" directory changed.'
            . ' Before clear cache executes "chilicon:permissions:change" command.'
            )
            ->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Group of the files and directories.')
            ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User of the files and directories (default to current system user).')
            ->addOption('filemode', null, InputOption::VALUE_OPTIONAL, 'Mode of the files (default 664).')
            ->addOption('dirmode', null, InputOption::VALUE_OPTIONAL, 'Mode of the directories (default 775).')
            ->addOption('sudo', 'S', InputOption::VALUE_NONE, 'Run command as sudo (default false).')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Run command as sudo (default false).')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $dirs = [
            $this->getRootDir() . '/var',
        ];

        $username = $input->getOption('user');
        if (empty($username)) {
            $username = $this->getSystemUsername();
        }

        $group = $input->getOption('group');
        if (empty($group)) {
            throw new \Exception('Group name is required. Please specify "--group=<GROUP_NAME>".');
        }

        $filemode = $input->getOption('filemode');
        if (empty($filemode)) {
            $filemode = '664';
        }

        $dirmode = $input->getOption('dirmode');
        if (empty($dirmode)) {
            $dirmode = '775';
        }

        $isSudo = $input->getOption('sudo');
        $isTest = $input->getOption('test');

        chdir($this->getRootDir());

        $output->writeln('Clear cache in safe way...' . "\n");

        ChangePermissionsCommand::changePermissions($input, $output, $dirs, $username, $group, $filemode, $dirmode, $isSudo, $isTest);

        $cmd = $this->getClearCacheCommand();
        $output->writeln(($isTest ? 'TEST:' : 'EXEC:') . ' ' . $cmd);
        if (! $isTest) {
            passthru($cmd);
        }

        $output->write("\n");

        ChangePermissionsCommand::changePermissions($input, $output, $dirs, $username, $group, $filemode, $dirmode, $isSudo, $isTest);

        $output->writeln("\nCOMPLETE :)\n");
    }

    /**
     * Creates command for clear Pimcore cache.
     * @return string Command for clear Pimcore cache.
     */
    private function getClearCacheCommand(): string
    {
        return $this->getRootDir() . '/bin/console cache:clear';
    }
}
