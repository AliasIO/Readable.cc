<?php

namespace Swiftlet\Models;

class Session extends \Swiftlet\Model
{
	public function __construct(\Swiftlet\Interfaces\App $app)
	{
		parent::__construct($app);

		session_start();
	}

	/**
	 * Set a session value
	 * @param string $variable
	 * @param mixed $value
	 */
	public function set($variable, $value)
	{
		$_SESSION[$variable] = $value;
	}

	/**
	 * Get a session value
	 * @param string $variable
	 * @return mixed
	 */
	public function get($variable)
	{
		if ( isset($_SESSION[$variable]) ) {
			return $_SESSION[$variable];
		}
	}

	/**
	 * Clear all session values
	 */
	public function clear()
	{
		session_regenerate_id(true);

		$_SESSION = array();
	}

	/**
	 * Get session ID
	 * @return string
	 */
	public function getId()
	{
		return session_id();
	}
}
