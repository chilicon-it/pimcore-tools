<?php

namespace Chilicon\Pimcore\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Creates database dump used by current Pimcore instance.
 *
 * @author Yaroslav Chupikov
 * @copyright (C) 2018 Chilicon IT
 * @version 1.0.0
 */
class ChangePermissionsCommand extends AbstractCommand
{
    const COMMAND_NAME = 'chilicon:permissions:change';
    const VERSION = '1.0.0';

    private $dirs = [];

    public function __construct()
    {
        $this->dirs = [
            $this->getRootDir() . '/var',
            $this->getRootDir() . '/web/var',
        ];
        parent::__construct();
    }

    public function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * Changes permissions for given directories.
     *
     * Specified directories must be defined as absolute paths.
     *
     * @param InputInterface $input Input.
     * @param OutputInterface $output Output.
     * @param array $dirs List of directories to change permisions.
     * @param string $username System username.
     * @param string $group System group.
     * @param string $filemode Access mode for files.
     * @param string $dirmode Access mode for directories.
     * @param bool $isSudo If true prepend commands with 'sudo'.
     * @param bool $isTest If true then do not execute commands.
     * @throws \Exception
     */
    public static function changePermissions(
        InputInterface $input,
        OutputInterface $output,
        array $dirs,
        string $username,
        string $group,
        string $filemode,
        string $dirmode,
        bool $isSudo,
        bool $isTest
    ) {

        if (empty($dirs)) {
            throw new \Exception('List of directories is required. Please specify "--dir=<RELATIVE_DIRECTORY>".');
        }

        echo 'USER     : ' . $username . "\n"
            . 'GROUP    : ' . $group . "\n"
            . 'FILEMODE : ' . $filemode . "\n"
            . 'DIRMODE  : ' . $dirmode . "\n"
            . 'SUDO     : ' . ($isSudo ? 'yes' : 'no') . "\n"
            . "\n"
        ;

        $cmd = self::getChownCommand($dirs, $username, $group, $isSudo);
        echo ($isTest ? 'TEST:' : 'RUN:') . ' ' . $cmd . "\n";
        if (! $isTest) {
            exec($cmd, $out);
        }

        foreach ($dirs as $dir) {

            $cmd = self::getChmodFilesCommand($dir, $filemode, $isSudo);
            echo ($isTest ? 'TEST:' : 'RUN:') . ' ' . $cmd . "\n";
            if (! $isTest) {
                exec($cmd, $out);
            }

            $cmd = self::getChmodDirsCommand($dir, $dirmode, $isSudo);
            echo ($isTest ? 'TEST:' : 'RUN:') . ' ' . $cmd . "\n";
            if (! $isTest) {
                exec($cmd, $out);
            }
        }
    }

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Change permissions for directories "var" and "web/var".')
            ->setHelp(
                'Change permissions for directories "var" and "web/var"'
                . ' in purpose of write by some system user and web server process.'
            )
            ->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Group of the files and directories.')
            ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User of the files and directories (default to current system user).')
            ->addOption('filemode', null, InputOption::VALUE_OPTIONAL, 'Mode of the files (default 664).')
            ->addOption('dirmode', null, InputOption::VALUE_OPTIONAL, 'Mode of the directories (default 775).')
            ->addOption('sudo', 'S', InputOption::VALUE_NONE, 'Run command as sudo (default false).')
            ->addOption('dir', 'd', InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY, 'Directory to change permissions (relative to project root directory).')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Run command as sudo (default false).')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

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

        $dirs = $input->getOption('dir');
        if (! empty($dirs)) {
            $this->dirs = [];
            foreach ($dirs as $dir) {
                if (empty($dir)) {
                    continue;
                }
                $this->dirs[] = $this->getRootDir() . ($dir[0] === '/' ? '' : '/') . $dir;
            }
        }

        $isSudo = $input->getOption('sudo');
        $isTest = $input->getOption('test');

        self::changePermissions($input, $output, $this->dirs, $username, $group, $filemode, $dirmode, $isSudo, $isTest);

        echo "\nCOMPLETE :)\n\n";
    }

    /**
     * Creates 'chown' command for goven directories.
     *
     * @param array $dirs Directories.
     * @param string $username System username.
     * @param string $group system group.
     * @param bool $isSudo If true prepend commands with 'sudo'.
     * @return string 'chown' command.
     */
    private static function getChownCommand(array $dirs, string $username, string $group, bool $isSudo): string
    {
        return ($isSudo ? 'sudo ' : '') . "chown -R {$username}:{$group} " . implode(' ', $dirs);
    }

    /**
     * Creates console command for change access mode for files.
     *
     * @param string $dir Directory to process.
     * @param string $filemode Access mode for files.
     * @param bool $isSudo If true prepend commands with 'sudo'.
     * @return string Console command for change access mode for files.
     */
    private static function getChmodFilesCommand(string $dir, string $filemode, bool $isSudo): string
    {
        return ($isSudo ? 'sudo ' : '') . "find {$dir} -type f -exec chmod {$filemode} {} \;";
    }

    /**
     * Creates console command for change access mode for directories.
     *
     * @param string $dir Directory to process.
     * @param string $dirmode Access mode for directories.
     * @param bool $isSudo If true prepend commands with 'sudo'.
     * @return string Console command for change access mode for directories.
     */
    private static function getChmodDirsCommand(string $dir, string $dirmode, bool $isSudo): string
    {
        return ($isSudo ? 'sudo ' : '') . "find {$dir} -type d -exec chmod {$dirmode} {} \;";
    }
}
