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

		$this->app->getSingleton('learn')->learn($userId);

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
				users_items.user_id = :user_id AND
				( users_items.read != 1 OR users_items.read IS NULL )
      ORDER BY score DESC, items.posted_at ASC
			LIMIT 10
			;');

		$sth->bindParam('user_id', $userId);

		$sth->execute();

		$result = $sth->fetchAll(\PDO::FETCH_OBJ);

		$items = $result;

		foreach ( $items as $item ) {
			$this->purify($item->contents);
		}

		$this->view->set('items', $items);
	}
}
