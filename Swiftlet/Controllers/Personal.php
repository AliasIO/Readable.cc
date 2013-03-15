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

		$this->view->name = 'feed-items';

		$this->getItems();
	}

	/**
	 * Register vote
	 */
	public function vote()
	{
		header('Content-type: application/json');

		$userId = $this->app->getSingleton('helper')->ensureValidUser(true);

		$itemId = isset($_POST['item_id']) ? (int) $_POST['item_id'] : null;
		$vote   = isset($_POST['vote'])    ? (int) $_POST['vote']    : null;

		if ( !$itemId || $vote < -1 || $vote > 1 ) {
			header('HTTP/1.0 400 Bad Request');

			exit(json_encode(array('error' => 'Invalid arguments')));
		}

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
      INSERT IGNORE INTO users_items (
        user_id,
        item_id,
        vote
      ) VALUES (
				:user_id,
				:item_id,
				:vote
      )
      ON DUPLICATE KEY UPDATE
        vote = :vote
			;');

		$sth->bindParam('user_id', $userId);
		$sth->bindParam('item_id', $itemId);
		$sth->bindParam('vote',    $vote);

		try {
			$sth->execute();
		} catch ( \Exception $e ) {
			header('HTTP/1.0 500 Server Error');

			exit(json_encode(array('error' => 'Something went wrong, please try again.')));
		}

		$sth = $dbh->prepare('
			SELECT
		 		vote
			FROM users_items
			WHERE
				user_id = :user_id AND
				item_id = :item_id
			LIMIT 1
			;');

		$sth->bindParam('user_id', $userId);
		$sth->bindParam('item_id', $itemId);

		try {
			$sth->execute();
		} catch ( \Exception $e ) {
			header('HTTP/1.0 500 Server Error');

			exit(json_encode(array('error' => 'Something went wrong, please try again.')));
		}

		$result = $sth->fetch(\PDO::FETCH_OBJ);

		exit(json_encode(array('vote' => $result->vote)));
	}

	/**
	 * Mark item as read
	 */
	public function read()
	{
		header('Content-type: application/json');

		$userId = $this->app->getSingleton('helper')->ensureValidUser(true);

		$itemId = isset($_POST['item_id']) ? (int) $_POST['item_id'] : null;
		$read   = isset($_POST['read'])    ? (int) $_POST['read']    : null;

		if ( !$itemId || ( $read != 0 && $read != 1 ) ) {
			header('HTTP/1.0 400 Bad Request');

			exit(json_encode(array('error' => 'Invalid arguments')));
		}

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
      INSERT IGNORE INTO users_items (
        user_id,
        item_id,
        `read`
      ) VALUES (
				:user_id,
				:item_id,
				:read
      )
      ON DUPLICATE KEY UPDATE
        `read` = :read
			;');

		$sth->bindParam('user_id', $userId);
		$sth->bindParam('item_id', $itemId);
		$sth->bindParam('read',    $read);

		try {
			$sth->execute();
		} catch ( \Exception $e ) {
			header('HTTP/1.0 500 Server Error');

			exit(json_encode(array('error' => 'Something went wrong, please try again.')));
		}

		exit;
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
				feeds.title AS feed_title,
				feeds.link  AS feed_link,
				items.id,
				items.url,
				items.title,
				items.contents,
				items.posted_at,
				users_items.vote,
				users_items.score
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
