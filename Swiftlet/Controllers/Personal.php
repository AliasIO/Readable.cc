<?php

namespace Swiftlet\Controllers;

class Personal extends \Swiftlet\Controller
{
	protected
		$title = 'Personal'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		if ( !$this->app->getSingleton('session')->get('id') ) {
			header('Location: ' . $this->app->getRootPath() . 'signin');

			exit;
		}
	}
}
