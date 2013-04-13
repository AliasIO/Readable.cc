<?php

namespace Swiftlet\Controllers;

class About extends \Swiftlet\Controller
{
	protected
		$title = 'About'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$this->view->set('pageDescription', 'Learn more about Readable.cc, the readable RSS reader.');
	}
}
