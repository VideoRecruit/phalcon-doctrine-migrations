<?php

namespace VideoRecruit\Phalcon\Doctrine\Migrations\DI;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Nette\DI\Config\Helpers as ConfigHelpers;
use Phalcon\Config;
use Phalcon\DiInterface;
use Symfony\Component\Console\Application;
use VideoRecruit\Phalcon\Doctrine\Migrations\InvalidArgumentException;
use VideoRecruit\Phalcon\Doctrine\Migrations\InvalidStateException;

/**
 * Class MigrationsExtension
 *
 * @package VideoRecruit\Phalcon\Doctrine\DI
 */
class MigrationsExtension
{
	const PREFIX_COMMAND = 'videorecruit.doctrine.migrations.command.';

	const CONFIGURATION = 'videorecruit.doctrine.migrations.configuration';
	const CONSOLE_COMMANDS = 'videorecruit.doctrine.migrations.commands';

	/**
	 * @var DiInterface
	 */
	private $di;

	/**
	 * @var array
	 */
	public $defaults = [
		'table' => 'doctrine_migrations',
		'column' => 'version',
		'directory' => __DIR__ . '/../../../../../../../../migrations',
		'namespace' => 'Migrations',
	];

	/**
	 * @var array
	 */
	private static $commands = [
		'Diff',
		'Execute',
		'Generate',
		'Latest',
		'Migrate',
		'Status',
		'Version',
	];

	/**
	 * DoctrineOrmExtension constructor.
	 *
	 * @param DiInterface $di
	 * @param array|Config $config
	 * @throws InvalidArgumentException
	 */
	public function __construct(DiInterface $di, $config)
	{
		$this->di = $di;

		if ($config instanceof Config) {
			$config = $config->toArray();
		} elseif (!is_array($config)) {
			throw new InvalidArgumentException('Config has to be either an array or ' .
				'a configuration instance.');
		}

		$config = $this->mergeConfigs($config, $this->defaults);

		$this->loadConfiguration($config);
		$this->loadCommands();
	}

	/**
	 * Register doctrine migrations.
	 *
	 * @param DiInterface $di
	 * @param array|Config $config
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function register(DiInterface $di, $config = NULL)
	{
		return new self($di, $config ?: []);
	}

	/**
	 * Helper to add all available commands into the console application.
	 *
	 * @param Application $consoleApp
	 * @param DiInterface $di
	 * @return Application
	 * @throws InvalidStateException
	 */
	public static function addCommands(Application $consoleApp, DiInterface $di)
	{
		if (!$di->has(self::CONSOLE_COMMANDS)) {
			throw new InvalidStateException('There are no migration commands. Did you register the extension before?');
		}

		$connectionHelper = $consoleApp->getHelperSet()->get('connection');
		$connection = $connectionHelper->getConnection();

		foreach ($di->get(self::CONSOLE_COMMANDS) as $serviceName) {
			$consoleApp->add($di->get($serviceName, [$connection]));
		}

		return $consoleApp;
	}

	/**
	 * @param array $config
	 */
	private function loadConfiguration(array $config)
	{
		$this->di->setShared(self::CONFIGURATION, function ($connection, $outputWriter = NULL) use ($config) {
			$configuration = new Configuration($connection, $outputWriter);
			$configuration->setMigrationsTableName($config['table']);
			$configuration->setMigrationsColumnName($config['column']);
			$configuration->setMigrationsDirectory($config['directory']);
			$configuration->setMigrationsNamespace($config['namespace']);

			return $configuration;
		});
	}

	/**
	 * Load commands and register to the DI container.
	 */
	private function loadCommands()
	{
		$commands = [];

		foreach (self::$commands as $name) {
			$serviceName = self::PREFIX_COMMAND . $name;

			$this->di->setShared($serviceName, function ($connection) use ($name) {
				$className = "Doctrine\\DBAL\\Migrations\\Tools\\Console\\Command\\{$name}Command";

				$command = new $className;

				$configuration = $this->get(self::CONFIGURATION, [$connection]);
				$command->setMigrationConfiguration($configuration);

				return $command;
			});

			$commands[$name] = $serviceName;
		}

		$this->di->setShared(self::CONSOLE_COMMANDS, new Config($commands));
	}

	/**
	 * Merge two configs.
	 *
	 * @param array $config
	 * @param array $defaults
	 * @return array
	 */
	private function mergeConfigs(array $config, array $defaults)
	{
		return ConfigHelpers::merge($config, $defaults);
	}
}
