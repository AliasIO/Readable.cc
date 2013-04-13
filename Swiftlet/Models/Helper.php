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
	 * Apply local time-zone offset to UTC date-time
	 *
	 * @param string $dateTime
	 * @return int
	 */
	public function localize(&$dateTime)
	{
		$dateTime = strtotime($dateTime) + $this->app->getSingleton('session')->get('timezone');
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

	/**
	 * Set controller name on view
	 *
	 * @param object $controller
	 */
	public function viewSetControllerName($controller, $view)
	{
		$view->set('controller', strtolower(preg_replace('/^Swiftlet\\\Controllers\\\/', '', get_class($controller))));
	}

	/**
	 * Generate direct link to feed
	 *
	 * @param object $controller
	 */
	public function getFeedLink($id, $title)
	{
		return '/feed/view/' . $id . '/' . trim(preg_replace('/--+/', '-', preg_replace('/[^a-z0-9]/', '-', strtolower( html_entity_decode($title, ENT_QUOTES, 'UTF-8')))), '-');
	}
}
