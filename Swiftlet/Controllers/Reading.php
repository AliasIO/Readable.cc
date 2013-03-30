<?php

namespace Swiftlet\Controllers;

class Reading extends \Swiftlet\Controllers\Read
{
	protected
		$title = 'My Reading'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$userId = $this->app->getSingleton('helper')->ensureValidUser();

		$this->getItems();
	}

	/**
	 * Get personal items
	 */
	public function items()
	{
		$this->app->getSingleton('helper')->ensureValidUser();

		$this->view->name = 'read';

		$this->getItems();
	}

	/**
	 * Get personal items
	 */
	protected function getItems()
	{
		$userId = $this->app->getSingleton('session')->get('id');

		$excludes = !empty($_GET['excludes']) ? explode(' ', $_GET['excludes']) : array();

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
      SELECT
				feeds.id    AS feed_id,
				feeds.title AS feed_title,
				feeds.link  AS feed_link,
				items.id,
				items.url,
				items.title,
				items.contents,
				items.posted_at,
				COALESCE(users_items.vote,  0) AS vote,
				COALESCE(users_items.score, 0) AS score,
				COALESCE(users_items.saved, 0) AS saved,
				1 AS feed_subscribed
			FROM             items
      INNER JOIN users_feeds ON users_feeds.feed_id = items.feed_id
      INNER JOIN       feeds ON       feeds.id      = items.feed_id
			LEFT  JOIN users_items ON users_items.item_id = items.id      AND users_items.user_id = ?
			WHERE
				  users_feeds.user_id = ?                               AND
				( users_items.read    = 0 OR users_items.read IS NULL )
				' . ( $excludes ? 'AND items.id NOT IN ( ' . implode(', ', array_fill(0, count($excludes), '?')) . ' )' : '' ) . '
      ORDER BY DATE(items.posted_at) DESC, users_items.score DESC
			LIMIT 10
			;');

		$i = 1;

		$sth->bindParam($i ++, $userId);
		$sth->bindParam($i ++, $userId);

		foreach( $excludes as $key => $itemId ) {
			$sth->bindParam($i ++, $excludes[$key]);
		}

		$sth->execute();

		$result = $sth->fetchAll(\PDO::FETCH_OBJ);

		$items = $result;

		$this->prepare($items);

		$this->view->set('items', $items);
	}
}
