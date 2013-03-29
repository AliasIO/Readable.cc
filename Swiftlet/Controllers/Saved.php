<?php

namespace Swiftlet\Controllers;

class Saved extends \Swiftlet\Controllers\Read
{
	protected
		$title = 'Saved'
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
		$page     = !empty($_GET['page']) ? (int) $_GET['page'] : 1;

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare($sql='
      SELECT
				feeds.id    AS feed_id,
				feeds.title AS feed_title,
				feeds.link  AS feed_link,
				items.id,
				items.url,
				items.title,
				items.contents,
				items.posted_at,
				users_items.saved,
				users_items.vote,
				users_items.score,
				IF(users_feeds.id IS NULL, 0, 1) AS feed_subscribed
			FROM       users_items
			INNER JOIN       items ON       items.id      = users_items.item_id
      INNER JOIN       feeds ON       feeds.id      = items.feed_id
      LEFT  JOIN users_feeds ON users_feeds.feed_id = feeds.id
			WHERE
				users_items.user_id = :user_id AND
				users_items.saved   = 1
				' . ( $excludes ? 'AND items.id NOT IN ( ' . implode(', ', array_fill(0, count($excludes), '?')) . ' )' : '' ) . '
      ORDER BY DATE(items.posted_at) DESC
			LIMIT ' . ( ( $page - 1 ) * self::ITEMS_PER_PAGE ) . ', ' . ( $page * self::ITEMS_PER_PAGE ) . '
			;');

		$sth->bindParam('user_id', $userId);

		$sth->execute();

		$result = $sth->fetchAll(\PDO::FETCH_OBJ);

		$items = $result;

		$this->prepare($items);

		$this->view->set('items', $items);
	}
}
