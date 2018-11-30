<?php

namespace Chilicon\Pimcore\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates database dump used by current Pimcore instance.
 *
 *  @TODO: Implement creating database
 *
 * @author Yaroslav Chupikov
 * @copyright (C) 2018 Chilicon IT
 * @version 1.0.0
 */
class DbDumpCommand extends Command
{
    const DEFAULT_PATH = '/chilicon-it/{host}';
    const COMMAND_NAME = 'chilicon:db:dump';

    /**
     * Project root directory.
     * @var string
     */
    private $rootDir = '';

    /**
     * Shared input nterface.
     * @var InputInterface
     */
    private $input;

    /**
     * Shared output nterface.
     * @var OutputInterface
     */
    private $output;

    /**
     * Command config.
     * @var array
     */
    private $config = [];

    public function __construct()
    {
        $this->rootDir = realpath(__DIR__ . '/../../../../..');
        $this->loadConfig();
        parent::__construct();
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
    }

    /**
     * Loads configuration from file '/var/config/chilicon-it.php'.
     */
    private function loadConfig()
    {
        $filename = $this->rootDir . '/var/config/chilicon-it.php';

        if (!is_file($filename) || !is_readable($filename)) {
            return;
        }

        $this->config = require($filename);
    }

    /**
     * Replace in the specified string substitutions from specified values array.
     *
     * For example:
     *
     * <code>
     * $path = $this->substReplace('/{dir}/{file}', [
     *      'dir' => 'tmp',
     *      'file' => 'some-file.txt',
     * ]);
     *
     * // $path contains '/tmp/some-file.txt'
     * </code>
     *
     * @param string $string String to process.
     * @param array $values Values to be inserted.
     * @return string Processed string.
     */
    private function substReplace(string $string, array $values): string
    {
        if (empty($values)) {
            return $string;
        }

        foreach ($values as $name => $value) {
            $string = str_replace('{' . $name . '}', $value, $string);
        }

        return $string;
    }

    /**
     * Loads and returns database credentials from configuration file '/var/config/system.php'
     *
     * @throws \Exception
     * @return array Database credentials loaded from configuration file '/var/config/system.php'
     */
    private function getDatabaseCredentials(): array
    {
        $filename = $this->rootDir . '/var/config/system.php';

        if (!is_file($filename)) {
            throw new \Exception("System config file not found: '{$filename}'.");
        } elseif (!is_readable($filename)) {
            throw new \Exception("System config file not readable: '{$filename}'.");
        }

        $this->output->writeln("Loading database configuration from '{$filename}'...");

        $config = require($filename);

        $params = $config['database']['params'];

        if (! is_array($params)) {
            throw new \Exception("Database connection credentials not found in the file: '{$filename}'.");
        } elseif (! array_key_exists('username', $params)) {
            throw new \Exception("Database username not found in the file: '{$filename}'.");
        } elseif (! array_key_exists('password', $params)) {
            throw new \Exception("Database password not found in the file: '{$filename}'.");
        } elseif (! array_key_exists('dbname', $params)) {
            throw new \Exception("Database name not found in the file: '{$filename}'.");
        }

        if (! array_key_exists('host', $params)) {
            $params['host'] = 'localhost';
        }
        if (! array_key_exists('port', $params)) {
            $params['port'] = 3306;
        }

        return $params;
    }

    /**
     * Returns name of the current host or alias of the current host
     * if defined in the '/var/config/chilicon-it.php' configuration file.
     *
     * @return string Returns name of the current host or alias of the current host.
     */
    private function getHostname(): string
    {
        if (isset($this->config['hostname']) && !empty($this->config['hostname'])) {
            return $this->config['hostname'];
        }

        $hostname = gethostname();

        if (isset($this->config['hostalias']) && isset($this->config['hostalias'][$hostname])) {
            $hostname = $this->config['hostalias'][$hostname];
        }

        return $hostname;
    }

    /**
     * Returns path to project directory for current machine (hostname).
     * Method replaces '{host}' with current host name or alias assigned to current host name.
     *
     * @return string Path to project directory for current machine (hostname).
     */
    private function getProjectPath(): string
    {
        if (array_key_exists('path', $this->config) && ! empty($this->config['path'])) {
            $path = $this->config['path'];
        } else {
            $path = self::DEFAULT_PATH;
        }

        return $this->rootDir . $this->substReplace($path, [
            'host' => $this->getHostname(),
        ]);
    }

    /**
     * Returns database dump files directory for current machine (host).
     *
     * @throws \Exception
     * @return string Database dump files directory for current machine (host).
     */
    private function getDumpDirectory(): string
    {
        $dir = $this->getProjectPath() . '/database';

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
