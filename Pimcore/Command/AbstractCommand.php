<?php

namespace Chilicon\Pimcore\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Abstract class contains methods and fields common for all Chilicon IT Commands for Pimcore.
 *
 * @author Yaroslav Chupikov
 * @copyright (C) 2018 Chilicon IT
 * @version 1.0.0
 */
abstract class AbstractCommand extends Command
{
    const DEFAULT_PATH = '/chilicon-it/{host}';

    /**
     * Project root directory.
     * @var string
     */
    private $rootDir = '';

    /**
     * Current system username.
     * @var string
     */
    protected $systemUsername = null;

    /**
     * Shared input nterface.
     * @var InputInterface
     */
    protected $input;

    /**
     * Shared output nterface.
     * @var OutputInterface
     */
    protected $output;

    /**
     * Command config.
     * @var array
     */
    protected $config = [];

    public function __construct()
    {
        $this->loadConfig();
        parent::__construct();
    }

    /**
     * Returns version of the current Command.
     * @return string Version of the current Command.
     */
    public abstract function getVersion(): string;

    /**
     * Loads configuration from file '/var/config/chilicon-it.php'.
     */
    protected function loadConfig()
    {
        $filename = $this->getRootDir() . '/var/config/chilicon-it.php';

        if (!is_file($filename) || !is_readable($filename)) {
            return;
        }

        $this->config = require($filename);
    }

    /**
     * Project root directory.
     * @return string Project root directory.
     */
    protected function getRootDir(): string
    {
        if (empty($this->rootDir)) {
            $this->rootDir = realpath(__DIR__ . '/../../../../..');
        }
        return $this->rootDir;
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
    protected function substReplace(string $string, array $values): string
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
    protected function getDatabaseCredentials(): array
    {
        $filename = $this->getRootDir() . '/var/config/system.php';

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
     * Returns current system user name even if script run as sudoer process.
     * @return string Current system user name.
     */
    protected function getSystemUsername(): string
    {
        if ($this->systemUsername === null) {
            $this->systemUsername = trim(exec('echo $USER'));
        }
        return $this->systemUsername;
    }

    /**
     * Returns name of the current host or alias of the current host
     * if defined in the '/var/config/chilicon-it.php' configuration file.
     *
     * @return string Returns name of the current host or alias of the current host.
     */
    protected function getHostname(): string
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
    protected function getHostProjectPath(): string
    {
        if (array_key_exists('path', $this->config) && ! empty($this->config['path'])) {
            $path = $this->config['path'];
        } else {
            $path = self::DEFAULT_PATH;
        }

        return $this->getRootDir() . $this->substReplace($path, [
            'host' => $this->getHostname(),
        ]);
    }
}
