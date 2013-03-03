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
	}
}
