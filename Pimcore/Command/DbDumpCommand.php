<?php

namespace Chilicon\Pimcore\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates database dump used by current Pimcore instance.
 *
 * @author Yaroslav Chupikov
 * @copyright (C) 2018 Chilicon IT
 * @version 1.0.0
 */
class DbDumpCommand extends AbstractCommand
{
    const COMMAND_NAME = 'chilicon:db:dump';
    const VERSION = '1.0.0';

    public function getVersion(): string
    {
        return self::VERSION;
    }

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Creates database dump of the current Pimcore instance.')
            ->setHelp(
                'Creates database dump of the current Pimcore instance.'
                . ' and saves it to predefined directory (like "chilicon-it/<HOST_NAME>/database/<DB_NAME>-<YYMMDD>-<hhmmss>.sql").'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $cmd = $this->getMysqlDumpCommand();

        $output->writeln('Creating database dump...');
        $output->writeln('HOST: ' . $this->getHostname());
        $output->writeln('CMD : ' . preg_replace('/--password=\S*/', '--password=*****', $cmd));

        exec($cmd, $out);

        $output->writeln("\nCOMPLETE :)\n");
    }


    /**
     * Returns database dump files directory for current machine (host).
     *
     * @throws \Exception
     * @return string Database dump files directory for current machine (host).
     */
    private function getDumpDirectory(): string
    {
        $dir = $this->getHostProjectPath() . '/database';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!is_dir($dir)) {
            throw new \Exception("Unable create directory for database dump files: {$dir}");
        }

        return $dir;
    }

    /**
     * Returns console 'mysqldump' command string with all parameters.
     *
     * @return string Console 'mysqldump' command string with all parameters.
     */
    private function getMysqlDumpCommand(): string
    {
        $dbConf = $this->getDatabaseCredentials();

        $filename = $this->getDumpDirectory() . '/' . $dbConf['dbname'] . '-' . date('ymd-His') . '.sql';

        $cmd = 'mysqldump'
            . ' --host=' . $dbConf['host']
            . ' --port=' . $dbConf['port']
            . ' --user=' . $dbConf['username']
            . ' --password=' . $dbConf['password']
            . ' ' . $dbConf['dbname']
            . ' > ' . $filename
            ;

        return $cmd;
    }
}
