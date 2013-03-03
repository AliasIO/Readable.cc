<?php

namespace Swiftlet\Models;

class Auth extends \Swiftlet\Model
{
	const
		USER_NOT_FOUND     = 1,
		PASSWORD_INCORRECT = 2,
		EMAIL_IN_USE       = 3,
		EMAIL_INVALID      = 4
		;

	protected
		$bcryptCost = 10
		;

	/**
	 * Perform compatibility check
	 *
	 * @param object $app
	 */
	public function __construct(\Swiftlet\Interfaces\App $app)
	{
		parent::__construct($app);

		if ( CRYPT_BLOWFISH != 1 ) {
			throw new \Exception(__CLASS__ . ' requires PHP support for BCrypt');
		}
	}

	/**
	 * Authenticate
	 *
	 * @param string $email
	 * @param string $password
	 * @return object
	 */
	public function authenticate($email, $password)
	{
		if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
			throw new \Exception('Email address invalid', self::EMAIL_INVALID);
		}

		$user = $this->getUser($email);

		if ( !$user ) {
			throw new \Exception('User does not exist', self::USER_NOT_FOUND);
		}

		if ( crypt($password, $user->password) != $user->password ) {
			throw new \Exception('Password incorrect', self::PASSWORD_INCORRECT);
		}

		return $user;
	}

	/**
	 * Create a new user
	 *
	 * @param string $email
	 * @param string $password
	 * @return bool
	 */
	public function register($email, $password)
	{
		if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
			throw new \Exception('Email address invalid', self::EMAIL_INVALID);
		}

		$user = $this->getUser($email);

		if ( $user ) {
			throw new \Exception('Email address already in use', self::EMAIL_IN_USE);
		}

		$hash = $this->generateHash($password);

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			INSERT INTO users (
				email,
				password
			) VALUES (
				:email,
				:password
			)
			;');

		$sth->bindParam(':email',    $email);
		$sth->bindParam(':password', $hash);

		return $sth->execute();
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
				password = :password
			WHERE
				id    = :id OR
				email = :id
			LIMIT 1
			;');

		$sth->bindParam(':id', $id);

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
				password
			FROM users
			WHERE
				id    = :id OR
				email = :id
			LIMIT 1
			;');

		$sth->bindParam(':id', $id);

		$sth->execute();

		return $sth->fetch(\PDO::FETCH_OBJ);
	}

	protected function generateHash($password)
	{
    $salt = sprintf('$2a$%02d$', $this->bcryptCost) . strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

    return crypt($password, $salt);
	}
}
