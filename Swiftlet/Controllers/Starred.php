<?php

namespace Swiftlet\Controllers;

class Starred extends \Swiftlet\Controllers\Read
{
	protected
		$title = 'Starred'
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

		if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			$this->view->name = 'read';
		}

		$this->getItems();
	}

	/**
	 * Get personal items
	 */
	protected function getItems()
	{
		$userId = $this->app->getSingleton('session')->get('id');

		$excludes = !empty($_GET['excludes']) ? explode(' ', $_GET['excludes'])  : array();
		$page     = !empty($_GET['page'])     ? max(1, (int) abs($_GET['page'])) : 1;

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare($sql='
      SELECT
				feeds.id              AS feed_id,
				feeds.title           AS feed_title,
				feeds.link            AS feed_link,
				items.id,
				items.url,
				items.title,
				items.contents,
				items.posted_at,
				users_feeds.folder_id AS folder_id,
				users_items.saved     AS starred,
				IF(users_feeds.id IS NULL, 0, 1) AS feed_subscribed
			FROM       users_items
			STRAIGHT_JOIN       items ON       items.id      = users_items.item_id
      STRAIGHT_JOIN       feeds ON       feeds.id      = items.feed_id
      LEFT  JOIN users_feeds ON users_feeds.feed_id = feeds.id            AND users_feeds.user_id = ?
			WHERE
				users_items.user_id = ? AND
				users_items.saved   = 1
				' . ( $excludes ? 'AND items.id NOT IN ( ' . implode(', ', array_fill(0, count($excludes), '?')) . ' )' : '' ) . '
      ORDER BY DATE(IF(items.posted_at, items.posted_at, items.created_at)) DESC
			LIMIT ?, ?
			;');

		$i = 1;

		$sth->bindParam($i ++, $userId, \PDO::PARAM_INT);
		$sth->bindParam($i ++, $userId, \PDO::PARAM_INT);

		foreach( $excludes as $key => $itemId ) {
			$sth->bindParam($i ++, $excludes[$key], \PDO::PARAM_INT);
		}

		$limitFrom  = ( $page - 1 ) * self::ITEMS_PER_PAGE;
		$limitCount = self::ITEMS_PER_PAGE;

		$sth->bindParam($i ++, $limitFrom,  \PDO::PARAM_INT);
		$sth->bindParam($i ++, $limitCount, \PDO::PARAM_INT);

		$sth->execute();

		$result = $sth->fetchAll(\PDO::FETCH_OBJ);

		$items = $result;

		$this->prepare($items);

		$this->view->set('items', $items);
	}
}
