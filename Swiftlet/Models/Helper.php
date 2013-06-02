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

			header('Location: ' . $this->app->getRootPath() . 'signin');

			exit;
		}

		return $userId;
	}

	/**
	 * Get the current user's folders
	 *
	 * @return array
	 */
	public function getUserFolders()
	{
		$userId = $this->app->getSingleton('session')->get('id');

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			SELECT
				folders.id,
				folders.title
			FROM       folders
			WHERE
		 		user_id = :user_id
			ORDER BY folders.title
			LIMIT 1000
			;');

		$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

		$sth->execute();

		$folders = $sth->fetchAll(\PDO::FETCH_OBJ);

		return $folders;
	}

	/**
	 * Apply local time-zone offset to UTC date-time
	 *
	 * @param string $dateTime
	 * @return int
	 */
	public function localize(&$dateTime)
	{
		$dateTime = $dateTime ? strtotime($dateTime) + $this->app->getSingleton('session')->get('timezone') : 0;
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
	 * Generate direct link to feed
	 *
	 * @param int $id
	 * @param string $title
	 * @return string
	 */
	public function getFeedLink($id, $title)
	{
		return $this->app->getRootPath() . 'feed/view/' . $id . '/' . $this->getSlug($title);
	}

	/**
	 * Generate direct link to folder
	 *
	 * @param int $id
	 * @param string $title
	 * @return string
	 */
	public function getFolderLink($id, $title)
	{
		return $this->app->getRootPath() . 'folder/view/' . $id . '/' . $this->getSlug($title);
	}

	/**
	 * Extract words from string
	 *
	 * @param string $string
	 * @return array
	 */
	public function extractWords($string)
	{
		$string = trim(preg_replace('/\s+/', ' ', preg_replace('/\b([0-9]+.)\b/', ' ', preg_replace('/\W/', ' ', preg_replace('/&[a-z]+/', '', strtolower($string))))));

		return explode(' ', $string);
	}

	/**
	 * String to URL segment
	 *
	 * @param string $string
	 * @return string
	 */
	protected function getSlug($string)
	{
		return trim(preg_replace('/--+/', '-', preg_replace('/[^a-z0-9]/', '-', strtolower( html_entity_decode($string, ENT_QUOTES, 'UTF-8')))), '-');
	}
}
