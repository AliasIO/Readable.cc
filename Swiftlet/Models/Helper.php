<?php

namespace Swiftlet\Models;

class Helper extends \Swiftlet\Model
{
	/**
	 * Ensure the user is logged in
	 *
	 * @param bool $ajax
	 * @return int
	 */
	public function ensureValidUser($ajax = false)
	{
		if ( !( $userId = $this->app->getSingleton('session')->get('id') ) ) {
			header('HTTP/1.0 403 Forbidden');

			if ( $ajax ) {
				exit(json_encode(array('message' => 'You need to be logged in')));
			}

			header('Location: /signin');

			exit;
		}

		return $userId;
	}

	/**
	 * Send an email
	 *
	 * @param bool $ajax
	 * @return int
	 */
	public function sendMail($to, $subject, $message)
	{
		$headers = implode("\r\n", array(
			'Content-type: text/plain; charset=UTF-8',
			'From: '     . $this->app->getConfig('emailFrom'),
			'Reply-To: ' . $this->app->getConfig('emailFrom')
			));

		return mail($to, $subject, $message, $headers);
	}
}
