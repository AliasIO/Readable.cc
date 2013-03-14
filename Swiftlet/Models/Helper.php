<?php

namespace Swiftlet\Models;

class Helper extends \Swiftlet\Model
{
	/**
	 * Ensure the user is logged in
	 *
	 * @param bool $ajax
	 * @return int
	 */
	public function ensureValidUser($ajax = false)
	{
		if ( !( $userId = $this->app->getSingleton('session')->get('id') ) ) {
			header('HTTP/1.0 403 Forbidden');

			if ( $ajax ) {
				exit(json_encode(array('message' => 'You need to be logged in')));
			}

			header('Location: ' . $this->app->getRootPath() . 'signin');

			exit;
		}

		return $userId;
	}
}
