<?php

namespace Swiftlet\Controllers;

class Terms extends \Swiftlet\Controller
{
	protected
		$title = 'Terms & Conditions'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$this->view->businessDetails = $this->app->getConfig('businessDetails');
	}
}
