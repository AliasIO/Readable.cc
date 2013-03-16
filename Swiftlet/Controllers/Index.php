<?php

namespace Swiftlet\Controllers;

class Index extends \Swiftlet\Controllers\Read
{
	protected
		$title = 'Popular'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$this->getItems();
	}

	/**
	 * Get popular items
	 */
	public function items()
	{
		$this->view->name = 'read';

		$this->getItems();
	}

	/**
	 * Get popular items
	 */
	public function getItems()
	{
		$userId = $this->app->getSingleton('session')->get('id');

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$select = '
      SELECT
				feeds.id    AS feed_id,
				feeds.title AS feed_title,
				feeds.link  AS feed_link,
				items.id,
				items.url,
				items.title,
				items.contents,
				items.posted_at,
				0                      AS feed_subscribed,
				0                      AS vote,
				AVG(users_items.score) AS score
			FROM       users_items
			INNER JOIN       items ON items.id = users_items.item_id
      INNER JOIN       feeds ON feeds.id =       items.feed_id
			GROUP BY items.id
      ORDER BY DATE(items.posted_at) DESC, AVG(users_items.score) DESC
			LIMIT 100
			';

		if ( $userId ) {
			$select = '
				SELECT
					main.*,
					COALESCE(users_items.vote, 0),
					IF(users_feeds.id IS NULL, 0, 1) AS feed_subscribed
				FROM ( ' . $select . ' ) AS main
				LEFT JOIN users_items ON users_items.item_id = main.id
				LEFT JOIN items       ON       items.id      = users_items.item_id
				LEFT JOIN users_feeds ON users_feeds.feed_id = items.feed_id
				WHERE
					users_items.user_id = :user_id AND
					( users_items.read != 1 OR users_items.read IS NULL )
				';

			$sth = $dbh->prepare($select);

			$sth->bindParam('user_id', $userId);
		} else {
			$sth = $dbh->prepare($select);
		}

		$sth->execute();

		$result = $sth->fetchAll(\PDO::FETCH_OBJ);

		$items = $result;

		foreach ( $items as $item ) {
			$this->purify($item->contents);
		}

		$this->view->set('items', $items);
	}
}
