<?php

namespace Swiftlet\Controllers;

class Contact extends \Swiftlet\Controller
{
	protected
		$title = 'Contact'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$this->view->set('pageDescription', 'Get in touch with Readable.cc, the readable RSS reader.');
	}
}
