<?php

namespace Swiftlet\Plugins;

class Post extends \Swiftlet\Plugin
{
	/**
	 * Implementation of the actionBefore hook
	 */
	public function actionBefore()
	{
		if ( !empty($_POST) ) {
			$sessionId = !empty($_POST['sessionId']) ? $_POST['sessionId'] : '';

			if ( $sessionId == $this->app->getSingleton('session')->getId() ) {
				return;
			}

			header('HTTP/1.0 403 Forbidden');

			exit('Invalid session. Please go back and try again.');
		}
	}
}
