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
	public function getFolders()
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
			');

		$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

		$sth->execute();

		$folders = $sth->fetchAll(\PDO::FETCH_OBJ);

		$grouped = array('none' => (object) array(
			'folder' => null,
			'feeds'  => array()
			));

		foreach ( $folders as $folder ) {
			$grouped[$folder->id] = (object) array(
				'folder' => $folder,
				'feeds'  => array()
				);
		}

		$sth = $dbh->prepare('
			SELECT
				feeds.id,
				feeds.url,
				feeds.title,
				feeds.link,
				feeds.last_fetched_at,
				users_feeds.folder_id
			FROM       users_feeds
			INNER JOIN feeds       ON users_feeds.feed_id = feeds.id
			WHERE
				users_feeds.user_id = :user_id
			ORDER BY feeds.title
			LIMIT 10000
			');

		$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

		$sth->execute();

		$feeds = $sth->fetchAll(\PDO::FETCH_OBJ);

		foreach ( $feeds as $feed ) {
			$this->app->getSingleton('helper')->localize($feed->last_fetched_at);
		}

		foreach ( $feeds as $feed ) {
			$grouped[$feed->folder_id ?: 'none']->feeds[] = $feed;
		}

		return $grouped;
	}

	/**
	 * Get the number of unread items per folder and feed
	 *
	 * @return object
	 */
	public function getUnreadItems()
	{
		$userId = $this->app->getSingleton('session')->get('id');

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			SELECT
				users_feeds.feed_id,
				users_feeds.folder_id,
				COUNT(items.id) AS unread_items
			FROM       users_feeds
      INNER JOIN       items ON       items.feed_id = users_feeds.feed_id
			LEFT  JOIN users_items ON users_items.item_id =       items.id      AND users_items.user_id = :user_id
			WHERE
				users_feeds.user_id = :user_id AND ( users_items.read = 0 OR users_items.read IS NULL )
			GROUP BY
				users_feeds.feed_id
			');

		$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

		$sth->execute();

		$counts = $sth->fetchAll(\PDO::FETCH_OBJ);

		$unreadItems = (object) array(
			'total'   => 0,
			'folders' => array(),
			'feeds'   => array()
			);

		foreach ( $counts as $count ) {
			if ( !isset($unreadItems->folders[$count->folder_id]) ) {
				$unreadItems->folders[$count->folder_id ?: 'none'] = 0;
			}

			$unreadItems->total                                += (int) $count->unread_items;
			$unreadItems->folders[$count->folder_id ?: 'none'] += (int) $count->unread_items;
			$unreadItems->feeds[$count->feed_id]                = (int) $count->unread_items;
		}

		return $unreadItems;
	}

	/**
	 * Check if the user has currently valid payment
	 *
	 * @return boolean
	 */
	public function userPaid()
	{
		$userId = $this->app->getSingleton('session')->get('id');

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			SELECT
				1
			FROM payments
			WHERE
		 		user_id    = :user_id AND
				expires_at > UTC_TIMESTAMP()
			LIMIT 1
			');

		$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

		$sth->execute();

		$paid = $sth->fetchAll(\PDO::FETCH_OBJ);

		return (bool) count($paid);
	}

	/**
	 * Apply local time-zone offset to UTC date-time
	 *
	 * @param string $dateTime
	 * @return int
	 */
	public function localize(&$dateTime)
	{
		$dateTime = strtotime($dateTime) > 0 ? strtotime($dateTime) + $this->app->getSingleton('session')->get('timezone') : 0;
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

		return array_filter(explode(' ', $string));
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
