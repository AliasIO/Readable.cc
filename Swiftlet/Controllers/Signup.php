<?php

namespace Swiftlet\Controllers;

class Signup extends \Swiftlet\Controller
{
	protected
		$title = 'Create account'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$email          = isset($_POST['email'])           ? $_POST['email']           : '';
		$password       = isset($_POST['password'])        ? $_POST['password']        : '';
		$passwordRepeat = isset($_POST['password-repeat']) ? $_POST['password-repeat'] : '';

		if ( !empty($_POST) ) {
			$success = false;
			$error   = false;

			if ( $password != $passwordRepeat ) {
				$error = 'The provided passwords don\'t match, please try again.';

				$this->view->set('error-password', true);
				$this->view->set('error-password-repeat', true);
			} else {
				try {
					$auth = $this->app->getSingleton('auth');

					$user = $auth->register($email, $password);

					if ( $user ) {
						$session = $this->app->getSingleton('session');

						$session->set('id',       $user->id);
						$session->set('email',    $user->email);
						$session->set('timezone', $user->timezone);

						header('Location: /subscriptions/welcome');

						exit;
					} else {
						$error = 'An unknown error occured. Please try again.';
					}
				} catch ( \Exception $e ) {
					$error = 'An unknown error ocurred.'.$e->getMessage();;

					switch ( $e->getCode() ) {
						case $auth::EMAIL_INVALID:
							$error = 'Please provide a valid email address.';

							$this->view->set('error-email', true);

							break;
						case $auth::EMAIL_IN_USE:
							$error = 'The provided email address is already in use.';

							$this->view->set('error-email', true);

							break;
						case $auth::PASSWORD_EMPTY:
							$error = 'Please choose a password.';

							$this->view->set('error-password', true);

							break;
					}
				}
			}

			if ( $success ) {
				$this->view->set('success', $success);
			} else {
				$this->view->set('error', $error);

				$this->view->set('email', $email);
			}
		}
	}
}
