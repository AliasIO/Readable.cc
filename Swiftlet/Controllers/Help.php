<?php

namespace Swiftlet\Controllers;

class Help extends \Swiftlet\Controller
{
	protected
		$title = 'Help'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$this->view->set('pageDescription', 'Contact, support and information about Readable.cc, the readable RSS reader.');
	}
}
