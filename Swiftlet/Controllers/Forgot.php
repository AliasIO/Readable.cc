<?php

namespace Swiftlet\Controllers;

class Forgot extends \Swiftlet\Controller
{
	protected
		$title = 'Forgot password'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		if ( !empty($_POST) ) {
			$success = false;
			$error   = false;

			$exists = true;

			$email = isset($_POST['email']) ? $_POST['email'] : '';

			if ( $email ) {
				$dbh = $this->app->getSingleton('pdo')->getHandle();

				$activationCode = sha1(uniqid(mt_rand(), true));

				$sth = $dbh->prepare('
					UPDATE users SET
						activation_code = :activation_code,
						updated_at      = UTC_TIMESTAMP()
					WHERE
						activation_code_expires_at < UTC_TIMESTAMP() AND
						email = :email
					LIMIT 1
					;');

				$sth->bindParam('activation_code', $activationCode);
				$sth->bindParam('email',           $email);

				$sth->execute();

				if ( !$sth->rowCount() ) {
					$sth = $dbh->prepare('
						SELECT
							activation_code
						FROM users
						WHERE
							email = :email
						LIMIT 1
						;');

					$sth->bindParam(':email', $email);

					$sth->execute();

					$result = $sth->fetch(\PDO::FETCH_OBJ);

					if ( $result ) {
						$activationCode = $result->activation_code;
					} else {
						$exists = false;
					}
				}

				if ( $exists ) {
					$message =
						"Hi,\n\n" .
						"We received a request to reset your password on " . $this->app->getConfig('siteName') . ".\n\n" .
						"If this was you and you would like to receive a new password please visit the page at the following URL:\n\n" .
						"  " . $this->app->getConfig('websiteUrl') . "/forgot/verify/" . $activationCode . "\n\n" .
						"If you do did not request a new password please ignore this email."
						;

					$this->app->getSingleton('helper')->sendMail($email, 'Password reset request', $message);
				}

				$success = 'An email has been sent with instructions to recover your account.';
			} else {
				$error = 'Please provide you email address';

				$this->view->set('error-email', true);
			}

			$this->view->set('success', $success);
			$this->view->set('error',   $error);
		}
	}
}
