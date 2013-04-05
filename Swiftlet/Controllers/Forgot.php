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
						activation_code            = :activation_code,
						activation_code_expires_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 1 DAY),
						updated_at                 = UTC_TIMESTAMP()
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

	/**
	 * Password reset verification
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
				SELECT
					id,
					email,
					enabled
				FROM users
				WHERE
					activation_code            = :activation_code AND
					activation_code_expires_at > UTC_TIMESTAMP()
				LIMIT 1
				;');

			$sth->bindParam('activation_code', $activationCode);

			$sth->execute();

			$result = $sth->fetch(\PDO::FETCH_OBJ);

			if ( $result && ( $userId = $result->id ) && ( $email = $result->email ) ) {
				$enabled = $result->enabled;

				$password = substr(sha1(uniqid(mt_rand(), true)), 0, 12);

				$auth = $this->app->getSingleton('auth');

				$result = $auth->setPassword($userId, $password);

				if ( $result ) {
					if ( !$enabled ) {
						$sth = $dbh->prepare('
							UPDATE users SET
								enabled = 1
							WHERE
								id = :id
							LIMIT 1
							;');

						$sth->bindParam(':id', $id);

						$sth->execute();
					}

					$message =
						"Hi,\n\n" .
						"Your password " . $this->app->getConfig('siteName') . " has been reset.\n\n" .
						"You may now log in with your email address and the following password:\n\n" .
						"  " . $password . "\n\n" .
						"  " . $this->app->getConfig('websiteUrl') . "/signin" . "\n\n" .
						"Please change this password in your account settings."
						;

					$this->app->getSingleton('helper')->sendMail($email, 'Your new password', $message);

					$success = 'An email has been sent with a new password.';
				} else {
					$error = 'Sorry, something went wrong.';
				}
			} else {
				$error = 'The verfication code is invalid or has expired.';
			}
		} else {
			$error = 'No verification code.';
		}

		$this->view->set('success', $success);
		$this->view->set('error',   $error);
	}
}
