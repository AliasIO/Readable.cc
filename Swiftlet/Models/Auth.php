<?php

namespace Swiftlet\Models;

class Auth extends \Swiftlet\Model
{
	const
		USER_NOT_FOUND     = 1,
		USER_NOT_ENABLED   = 2,
		PASSWORD_EMPTY     = 3,
		PASSWORD_INCORRECT = 4,
		EMAIL_IN_USE       = 5,
		EMAIL_INVALID      = 6
		;

	protected
		$bcryptCost = 10
		;

	/**
	 * Perform compatibility check
	 *
	 * @param object $app
	 * @throws \Swiftlet\Exception
	 */
	public function __construct(\Swiftlet\Interfaces\App $app)
	{
		parent::__construct($app);

		if ( CRYPT_BLOWFISH != 1 ) {
			throw new \Swiftlet\Exception(__CLASS__ . ' requires PHP support for BCrypt');
		}
	}

	/**
	 * Authenticate
	 *
	 * @param string $email
	 * @param string $password
	 * @return object
	 * @throws \Swiftlet\Exception
	 */
	public function authenticate($email, $password)
	{
		if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
			throw new \Swiftlet\Exception('Email address invalid', self::EMAIL_INVALID);
		}

		$user = $this->getUser($email);

		if ( !$user ) {
			throw new \Swiftlet\Exception('User does not exist', self::USER_NOT_FOUND);
		}

		if ( crypt($password, $user->password) != $user->password ) {
			throw new \Swiftlet\Exception('Password incorrect', self::PASSWORD_INCORRECT);
		}

		if ( !$user->enabled && time() > strtotime($user->created_at) + 24 * 60 * 60 ) {
			throw new \Swiftlet\Exception('Account not enabled', self::USER_NOT_ENABLED);
		}

		return $user;
	}

	/**
	 * Create a new user
	 *
	 * @param string $email
	 * @param string $password
	 * @return bool
	 * @throws \Swiftlet\Exception
	 */
	public function register($email, $password)
	{
		if ( !$password ) {
			throw new \Swiftlet\Exception('No password specified', self::PASSWORD_EMPTY);
		}

		if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
			throw new \Swiftlet\Exception('Email address invalid', self::EMAIL_INVALID);
		}

		$user = $this->getUser($email);

		if ( $user ) {
			throw new \Swiftlet\Exception('Email address already in use', self::EMAIL_IN_USE);
		}

		$hash = $this->generateHash($password);

		$activationCode = sha1(uniqid(mt_rand(), true));

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			INSERT INTO users (
				email,
				password,
				created_at,
				updated_at,
				last_active_at,
				activation_code,
				activation_code_expires_at
			) VALUES (
				:email,
				:password,
				UTC_TIMESTAMP(),
				UTC_TIMESTAMP(),
				UTC_TIMESTAMP(),
				:activation_code,
				DATE_ADD(UTC_TIMESTAMP(), INTERVAL 1 DAY)
			)
			;');

		$sth->bindParam(':email',           $email);
		$sth->bindParam(':password',        $hash);
		$sth->bindParam(':activation_code', $activationCode);

		$result = $sth->execute();

		if ( $result ) {
			$message =
				"Hi,\n\n" .
				"Thanks for creating an account at " . $this->app->getConfig('siteName') . "!\n\n" .
				"Please verify your email address by visiting the page at the following URL:\n\n" .
				"  " . $this->app->getConfig('websiteUrl') . "/signin/verify/" . $activationCode . "\n\n" .
				"If you do not respond to this email within 24 hours your account will automatically be disabled.\n\n" .
				"--\n\n" .
				"Please reply to this email if you have any questions, suggestions or just want to say hi.\n\n" .
				"Follow " . $this->app->getConfig('siteName') . " on Twitter: https://twitter.com/" . $this->app->getConfig('twitterHandle')
				;

			$this->app->getSingleton('helper')->sendMail($email, 'Please verify your email address', $message);

			return $this->getUser($email);
		}
	}

	/**
	 * Update a user's password by ID or email address
	 *
	 * @param mixed $id
	 * @param string $password
	 * @return bool
	 */
	public function setPassword($id, $password)
	{
		$hash = $this->generateHash($password);

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			UPDATE users SET
				password   = :password,
				updated_at = UTC_TIMESTAMP()
			WHERE
				id    = :id OR
				email = :id
			LIMIT 1
			;');

		$sth->bindParam(':id',       $id, \PDO::PARAM_INT);
		$sth->bindParam(':password', $hash);

		return $sth->execute();
	}

	/**
	 * Get a user by ID or email address
	 *
	 * @param mixed $id
	 */
	public function getUser($id)
	{
		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			SELECT
				id,
				email,
				password,
				external_links,
				item_order,
				timezone,
				created_at,
				enabled
			FROM users
			WHERE
				id    = :id OR
				email = :id
			LIMIT 1
			;');

		$sth->bindParam(':id', $id, \PDO::PARAM_INT);

		$sth->execute();

		return $sth->fetch(\PDO::FETCH_OBJ);
	}

	protected function generateHash($password)
	{
		$salt = sprintf('$2a$%02d$', $this->bcryptCost) . strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

		return crypt($password, $salt);
	}
}
