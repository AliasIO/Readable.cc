<?php

namespace Swiftlet\Controllers;

class Personal extends \Swiftlet\Controllers\Read
{
	protected
		$title = 'Personal'
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
				users_items.vote,
				users_items.score,
				1 AS feed_subscribed
			FROM       users_items
			INNER JOIN       items ON items.id = users_items.item_id
      INNER JOIN       feeds ON feeds.id =       items.feed_id
			WHERE
				users_items.user_id = ? AND
				( users_items.read != 1 OR users_items.read IS NULL )
				' . ( $excludes ? 'AND items.id NOT IN ( ' . implode(', ', array_fill(0, count($excludes), '?')) . ' )' : '' ) . '
      ORDER BY DATE(items.posted_at) DESC, users_items.score DESC
			LIMIT 10
			;');

		$i = 1;

		$sth->bindParam($i ++, $userId);

		foreach( $excludes as $key => $itemId ) {
			$sth->bindParam($i ++, $excludes[$key]);
		}

		$sth->execute();

		$result = $sth->fetchAll(\PDO::FETCH_OBJ);

		$items = $result;

		foreach ( $items as $item ) {
			$this->purify($item->contents);
			$this->localize($item->posted_at);
		}

		$this->view->set('items', $items);

		if ( !$items ) {
			$this->view->name = 'personal-empty';
		}
	}
}
