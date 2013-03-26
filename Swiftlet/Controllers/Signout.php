<?php

namespace Swiftlet\Controllers;

class Signout extends \Swiftlet\Controller
{
	protected
		$title = 'Goodbye'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$this->app->getSingleton('session')->clear();

		if ( !empty($_COOKIE['session']) && preg_match('/^[0-9]+_[0-9a-f]+$/', $_COOKIE['session']) ) {
			setcookie('session', '', 0, '/');

			try {
				unlink('sessions/' . $_COOKIE['session'] . '.php');
			} catch ( \Exception $e ) {
			}
		}
	}
}
