<?php

namespace Swiftlet\Models;

class Pdo extends \Swiftlet\Model
{
	protected
		$handle
		;

	/**
	 * Establish database connection
	 *
	 * @param object $app
	 */
	public function __construct(\Swiftlet\Interfaces\App $app)
	{
		parent::__construct($app);

		require('config/pdo.php');

		$config = $this->app->getConfig('pdo');

		try {
			$this->handle = new \PDO($config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['database'], $config['username'], $config['password']);
		} catch ( \PDOException $e ) {
			throw new Exception('Error establishing database connection: ' . $e->getMessage());
		}

		$this->handle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Return database handle
	 */
	public function getHandle()
	{
		return $this->handle;
	}
}
