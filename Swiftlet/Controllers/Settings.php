<?php

namespace Swiftlet\Controllers;

class Settings extends \Swiftlet\Controller
{
	protected
		$title = 'Settings',
		$userId
		;

	/**
	 * Constructor
	 * @param object $app
	 * @param object $view
	 */
	public function __construct(\Swiftlet\Interfaces\App $app, \Swiftlet\Interfaces\View $view)
	{
		parent::__construct($app, $view);

		$this->userId = $this->app->getSingleton('helper')->ensureValidUser();

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			SELECT
				amount,
				currency,
				created_at,
				expires_at
			FROM payments
			WHERE
		 		user_id = :user_id
			ORDER BY created_at DESC
			LIMIT 1000
			');

		$sth->bindParam('user_id', $this->userId, \PDO::PARAM_INT);

		$sth->execute();

		$payments = $sth->fetchAll(\PDO::FETCH_OBJ);

		foreach ( $payments as $payment ) {
			$this->app->getSingleton('helper')->localize($payment->created_at);
			$this->app->getSingleton('helper')->localize($payment->expires_at);

			$payment->created_at = date('F j, Y', $payment->created_at);
			$payment->expires_at = date('F j, Y', $payment->expires_at);
		}

		$this->view->set('payments', $payments);

		$session = $this->app->getSingleton('session');

		$this->view->set('links',    $session->get('external_links'));
		$this->view->set('order',    $session->get('item_order'));
		$this->view->set('timezone', $session->get('timezone'));
		$this->view->set('email',    $session->get('email'));
		$this->view->set('paid',     $this->app->getSingleton('helper')->userPaid());

		$this->view->set('timeZones', array(
			'-720' => '(GMT -12:00) Eniwetok, Kwajalein',
			'-660' => '(GMT -11:00) Midway Island, Samoa',
			'-600' => '(GMT -10:00) Hawaii',
			'-540' => '(GMT -9:00) Alaska',
			'-480' => '(GMT -8:00) Pacific Time (US &amp; Canada)',
			'-420' => '(GMT -7:00) Mountain Time (US &amp; Canada)',
			'-360' => '(GMT -6:00) Central Time (US &amp; Canada), Mexico City',
			'-300' => '(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima',
			'-240' => '(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz',
			'-210' => '(GMT -3:30) Newfoundland',
			'-180' => '(GMT -3:00) Brazil, Buenos Aires, Georgetown',
			'-120' => '(GMT -2:00) Mid-Atlantic',
			'-60'  => '(GMT -1:00) Azores, Cape Verde Islands',
			'0'    => '(GMT) Western Europe Time, London, Lisbon, Casablanca',
			'60'   => '(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris',
			'120'  => '(GMT +2:00) Kaliningrad, South Africa',
			'180'  => '(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg',
			'210'  => '(GMT +3:30) Tehran',
			'240'  => '(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi',
			'270'  => '(GMT +4:30) Kabul',
			'300'  => '(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent',
			'330'  => '(GMT +5:30) Bombay, Calcutta, Madras, New Delhi',
			'345'  => '(GMT +5:45) Kathmandu',
			'360'  => '(GMT +6:00) Almaty, Dhaka, Colombo',
			'420'  => '(GMT +7:00) Bangkok, Hanoi, Jakarta',
			'480'  => '(GMT +8:00) Beijing, Perth, Singapore, Hong Kong',
			'540'  => '(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk',
			'570'  => '(GMT +9:30) Adelaide, Darwin',
			'600'  => '(GMT +10:00) Eastern Australia, Guam, Vladivostok',
			'660'  => '(GMT +11:00) Magadan, Solomon Islands, New Caledonia',
			'720'  => '(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka'
		));
	}

	/**
	 * Default action
	 */
	public function index()
	{
		if ( !empty($_POST['form']) ) {
			switch ( $_POST['form'] ) {
				case 'settings':
					$this->settings();

					break;
				case 'account':
					$this->account();

					break;
				case 'delete':
					$this->delete();

					break;
			}
		} else {
			if ( !$this->app->getSingleton('session')->get('enabled') ) {
				$this->view->set('error', 'Please verify your email address to fully activate your account. <a href="' . $this->app->getRootPath() . 'settings/verify">Resend verification email</a>.');
			}
		}
	}

	/**
	 * Resend verification email
	 */
	public function verify()
 	{
		$email = $this->app->getSingleton('session')->get('email');

		$this->app->getSingleton('auth')->verify($email);

		$this->view->set('success', 'Thank you, an email with instructions has been sent to ' . $email . '.');
	}

	/**
	 * Save settings
	 */
	protected function settings()
	{
		$session = $this->app->getSingleton('session');

		$links    = isset($_POST['links'])    ? $_POST['links']    : 0;
		$order    = isset($_POST['order'])    ? $_POST['order']    : 0;
		$timeZone = isset($_POST['timezone']) ? $_POST['timezone'] : 0;

		$success = false;
		$error   = array();

		if ( !$error ) {
			$dbh = $this->app->getSingleton('pdo')->getHandle();

			$sth = $dbh->prepare($sql='
				UPDATE users SET
					external_links = :external_links,
					item_order     = :item_order,
					timezone       = :timezone,
					updated_at     = UTC_TIMESTAMP()
				WHERE
					id = :id
				LIMIT 1
				');

			$sth->bindParam(':id',             $this->userId, \PDO::PARAM_INT);
			$sth->bindParam(':external_links', $links,        \PDO::PARAM_INT);
			$sth->bindParam(':item_order',     $order,        \PDO::PARAM_INT);
			$sth->bindParam(':timezone',       $timeZone,     \PDO::PARAM_INT);

			$sth->execute();

			$success = 'Your changes have been saved.';

			$session->set('external_links', $links);
			$session->set('item_order',     $order);
			$session->set('timezone',       $timeZone);
		}

		if ( $success ) {
			$this->view->set('success', $success);
		} else {
			$this->view->set('error', implode('<br>', $error));
		}

		$this->view->set('links',    $links);
		$this->view->set('order',    $order);
		$this->view->set('timezone', $timeZone);
	}

	/**
	 * Save account settings
	 *
	 * @throws \Swiftlet\Exception
	 */
	protected function account()
	{
		$session = $this->app->getSingleton('session');

		$email           = isset($_POST['email'])            ? $_POST['email']            : '';
		$password        = isset($_POST['password'])         ? $_POST['password']         : '';
		$passwordRepeat  = isset($_POST['password-repeat'])  ? $_POST['password-repeat']  : '';
		$currentPassword = isset($_POST['current-password']) ? $_POST['current-password'] : '';

		$success = false;
		$error   = array();

		try {
			$auth = $this->app->getSingleton('auth');

			$auth->authenticate($session->get('email'), $currentPassword);

			if ( $password || $passwordRepeat ) {
				if ( $password != $passwordRepeat ) {
					$error[] = 'The provided passwords don\'t match, please try again.';

					$this->view->set('error-password',        true);
					$this->view->set('error-password-repeat', true);
				} else {
					$auth->setPassword($this->userId, $password);
				}
			}

			if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
				$error[] = 'Please provide a valid email address.';
			}

			if ( !$error ) {
				$dbh = $this->app->getSingleton('pdo')->getHandle();

				$sth = $dbh->prepare('
					UPDATE users SET
						email      = :email,
						updated_at = UTC_TIMESTAMP()
					WHERE
						id = :id
					LIMIT 1
					;');

				$sth->bindParam(':id',    $this->userId, \PDO::PARAM_INT);
				$sth->bindParam(':email', $email);

				$sth->execute();

				$success = 'Your account has been updated.';

				$session->set('email', $email);
			}
		} catch ( \Swiftlet\Exception $e ) {
			switch ( $e->getCode() ) {
				case $auth::PASSWORD_INCORRECT:
					$error[] = 'Current password incorrect, please try again';

					$this->view->set('error-current-password', true);

					break;
				case $auth::USER_NOT_ENABLED:
					$error[] = 'Your changes could not be saved because your email address has not been verified. Please recover your account via the &lsquo;<a href="' . $this->app->getRootPath() . 'forgot">Forgot password</a>&rsquo; page.';

					break;
				default:
					$error[] = 'An unknown error ocurred.' . $e->getMessage();
			}
		}

		if ( $success ) {
			$this->view->set('success', $success);
		} else {
			$this->view->set('error', implode('<br>', $error));
		}

		$this->view->set('email',    $email);
	}

	/**
	 * Delete account
	 */
	protected function delete()
	{
		$this->userId = $this->app->getSingleton('helper')->ensureValidUser();

		$session = $this->app->getSingleton('session');

		$success = false;
		$error   = false;

		$password = isset($_POST['password-delete']) ? $_POST['password-delete'] : '';

		try {
			$auth = $this->app->getSingleton('auth');

			$auth->authenticate($session->get('email'), $password);

			$dbh = $this->app->getSingleton('pdo')->getHandle();

			$sth = $dbh->prepare('
				DELETE
				FROM users
				WHERE
					id = :user_id
				LIMIT 1
				;');

			$sth->bindParam('user_id', $this->userId, \PDO::PARAM_INT);

			$sth->execute();

			$success = 'Your account has been deleted.';

			$this->app->getSingleton('session')->clear();
		} catch ( \Exception $e ) {
			switch ( $e->getCode() ) {
				case $auth::PASSWORD_INCORRECT:
					$error = 'Password incorrect, please try again';

					$this->view->set('error-password-delete', true);

					break;
				default:
					$error = 'An unknown error ocurred.' . $e->getMessage();
			}
		}

		if ( $success ) {
			$this->view->set('success', $success);
		} else {
			$this->view->set('error', $error);
		}
	}

	/**
	 * Sort words alphabetically
	 *
	 * @param object @a
	 * @param object @b
	 * @return int
	 */
	protected function sortWords($a, $b)
	{
		return $a->word > $b->word ? 1 : ( $a->word < $b->word ? -1 : 0 );
	}
}
