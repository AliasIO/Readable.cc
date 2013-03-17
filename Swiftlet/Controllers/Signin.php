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

				$session->set('id',       $user->id);
				$session->set('email',    $user->email);
				$session->set('timezone', $user->timezone);

				$dbh = $this->app->getSingleton('pdo')->getHandle();

				$sth = $dbh->prepare('
					UPDATE users SET
						last_active_at = UTC_TIMESTAMP()
					WHERE
						id = :id
					LIMIT 1
					;');

				$sth->bindParam('id', $user->id);

				$sth->execute();

				header('Location: /personal');
			} catch ( \Exception $e ) {
				$error = 'An unknown error ocurred.';

				switch ( $e->getCode() ) {
					case $auth::EMAIL_INVALID:
						$error = 'Please provide a valid email address.';

						$this->view->set('error-email', true);

						break;
					case $auth::USER_NOT_FOUND:
					case $auth::PASSWORD_INCORRECT:
					case $auth::USER_NOT_ENABLED:
						$error = 'The provided email address or password is incorrect, please try again.';

						$this->view->set('error-email',    true);
						$this->view->set('error-password', true);

						break;
				}

				$this->view->set('error', $error);
			}
		}
	}

	/**
	 * Email verification
	 */
	public function verify()
	{
		$success = false;
		$error   = false;

		$args = $this->app->getArgs();

		if ( !empty($args[0]) ) {
			$activationCode = $args[0];

			$dbh = $this->app->getSingleton('pdo')->getHandle();

			$sth = $dbh->prepare('
				UPDATE users SET
					enabled = 1
				WHERE
					activation_code            = :activation_code AND
					activation_code_expires_at > UTC_TIMESTAMP()
				LIMIT 1
				;');

			$sth->bindParam('activation_code', $activationCode);

			$sth->execute();

			if ( $sth->rowCount() ) {
				$success = 'Thank you, your email address has been verified!';
			} else {
				$error = 'The verfication code is invalid, expired or has already been used. Please use the "Forgot password" link if you need to recover your account.';
			}
		} else {
			$error = 'No verification code.';
		}

		$this->view->set('success', $success);
		$this->view->set('error',   $error);
	}
}
