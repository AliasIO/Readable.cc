<?php

namespace Swiftlet\Models;

class Subscription extends \Swiftlet\Model
{
	/**
	 * Subscribe to a feed by ID or URL
	 *
	 * @param int $id
	 * @param string $url
	 * @return int
	 */
	public function subscribe($id, $url = '', $folderId = null)
	{
		$userId = $this->app->getSingleton('session')->get('id');

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		if ( $url ) {
			$sth = $dbh->prepare('
				SELECT
					id
				FROM feeds
				WHERE url = :url
				LIMIT 1
				');

			$sth->bindParam('url', $url);

			$sth->execute();

			$result = $sth->fetch(\PDO::FETCH_OBJ);

			if ( $result ) {
				$id = $result->id;
			} else {
				$feed = $this->app->getModel('feed');

				$feed->fetch($url)->save();

				$id = $feed->id;

				$itemIds = array();

				foreach ( $feed->getItems() as $item ) {
					if ( $item->getId() ) {
						$itemIds[] = $item->getId();
					}
				}

				$this->app->getsingleton('learn')->learn($itemIds);
			}
		}

		$sth = $dbh->prepare('
			INSERT IGNORE INTO users_feeds (
				user_id,
				feed_id,
				folder_id
			) VALUES (
				:user_id,
				:feed_id,
				:folder_id
			)
			');

		$sth->bindParam('user_id',   $userId,   \PDO::PARAM_INT);
		$sth->bindParam('feed_id',   $id,       \PDO::PARAM_INT);
		$sth->bindParam('folder_id', $folderId, \PDO::PARAM_INT);

		$sth->execute();

		return $id;
	}

	/**
	 * Unsubscribe from a feed by ID or URL
	 *
	 * @param int $id
	 * @param string $url
	 */
	public function unsubscribe($id, $url = '')
	{
		$userId = $this->app->getSingleton('session')->get('id');

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			DELETE
				users_feeds
			FROM      users_feeds
			STRAIGHT_JOIN      feeds ON feeds.id = users_feeds.feed_id
			WHERE
				users_feeds.user_id = :user_id AND
				' . ( $id ? 'feeds.id = :id' : 'feeds.url = :url' ) . '
			;');

		$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

		if ( $id ) {
			$sth->bindParam('id', $id, \PDO::PARAM_INT);
		} else {
			$sth->bindParam('url', $url);
		}

		$sth->execute();
	}

	/**
	 * Add subscription to folder
	 *
	 * @param int $id
	 * @param int $folderId
	 */
	public function folder($id, $folderId)
	{
		$userId = $this->app->getSingleton('session')->get('id');

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			UPDATE
				users_feeds
			SET
				folder_id = :folder_id
			WHERE
				user_id = :user_id AND
				feed_id = :feed_id
			LIMIT 1
			;');

		$sth->bindParam('user_id',   $userId,   \PDO::PARAM_INT);
		$sth->bindParam('feed_id',   $id,       \PDO::PARAM_INT);
		$sth->bindParam('folder_id', $folderId, \PDO::PARAM_INT);

		$sth->execute();
	}
}
