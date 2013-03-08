<?php

namespace Swiftlet\Controllers;

class Signin extends \Swiftlet\Controller
{
	protected
		$title = 'Please sign in'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$email    = isset($_POST['email'])    ? $_POST['email']    : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '';

		$this->view->set('email', $email);

		if ( !empty($_POST) ) {
			$error = false;

			try {
				$auth = $this->app->getSingleton('auth');

				$user = $auth->authenticate($email, $password);

				$session = $this->app->getSingleton('session');

				$session->set('id',    $user->id);
				$session->set('email', $user->email);

				header('Location: ' . $this->app->getRootPath() . 'personal');
			} catch ( \Exception $e ) {
				$error = 'An unknown error ocurred.';

				switch ( $e->getCode() ) {
					case $auth::EMAIL_INVALID:
						$error = 'Please provide a valid email address.';

						$this->view->set('error-email', true);

						break;
					case $auth::USER_NOT_FOUND:
					case $auth::PASSWORD_INCORRECT:
						$error = 'The provided email address or password is incorrect, please try again.';

						$this->view->set('error-email', true);
						$this->view->set('error-password', true);

						break;
				}

				$this->view->set('error', $error);
			}
		}
	}
}
