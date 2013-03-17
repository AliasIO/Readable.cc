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

		$args = $this->app->getArgs();

		$page     = !empty($_GET['page'])     ? (int) $_GET['page']             : 1;
		$excludes = !empty($_GET['excludes']) ? explode(' ', $_GET['excludes']) : array();

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$select = '
      SELECT
				feeds.id         AS feed_id,
				feeds.title      AS feed_title,
				feeds.link       AS feed_link,
				items.id,
				items.url,
				items.title,
				items.contents,
				items.posted_at,
				0                AS feed_subscribed,
				0                AS vote,
				COALESCE(AVG(users_items.score), 0) AS score
			FROM             items
      INNER JOIN       feeds ON       feeds.id      = items.feed_id
			LEFT  JOIN users_items ON users_items.item_id = items.id
			GROUP BY items.id
      ORDER BY DATE(items.posted_at) DESC, AVG(users_items.score) DESC
			';

		if ( $userId ) {
			$select = '
				SELECT
					main.*,
					COALESCE(users_items.vote, 0),
					IF(users_feeds.id IS NULL, 0, 1) AS feed_subscribed
				FROM ( ' . $select . ' ) AS main
				LEFT  JOIN items       ON       items.id      =  main.id
				LEFT  JOIN users_items ON users_items.item_id =  main.id      AND users_items.user_id = :user_id
				INNER JOIN users_feeds ON users_feeds.feed_id = items.feed_id
				WHERE
					users_items.read != 1 OR users_items.read IS NULL
				';
		}

		$select .= ' LIMIT ' . ( ( $page - 1 ) * self::ITEMS_PER_PAGE ) . ', ' . ( $page * self::ITEMS_PER_PAGE );

		$sth = $dbh->prepare($select);

		$sth->bindParam('user_id', $userId);

		$sth->execute();

		$result = $sth->fetchAll(\PDO::FETCH_OBJ);

		$items = $result;

		foreach ( $items as $item ) {
			$this->purify($item->contents);
			$this->localize($item->posted_at);
		}

		$this->view->set('items', $items);
	}
}
