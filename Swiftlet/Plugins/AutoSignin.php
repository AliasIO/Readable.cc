<?php

namespace Swiftlet\Plugins;

class AutoSignin extends \Swiftlet\Plugin
{
	/**
	 * Implementation of the actionBefore hook
	 */
	public function actionBefore()
	{
		if ( !$this->app->getSingleton('session')->get('id') ) {
			if ( !empty($_COOKIE['session']) ) {
				try {
					$contents = explode("\n", file_get_contents('sessions/' . $_COOKIE['session'] . '.php'));

					$userId = array_pop($contents);

					$user = $this->app->getSingleton('auth')->getUser($userId);

					if ( $user ) {
						$session = $this->app->getSingleton('session');

						$session->set('id',       $user->id);
						$session->set('email',    $user->email);
						$session->set('timezone', $user->timezone);
					}
				} catch ( \Exception $e ) {
				}
			}
		}
	}
}
