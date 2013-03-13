<?php

namespace Swiftlet\Controllers;

class Personal extends \Swiftlet\Controller
{
	protected
		$title = 'Personal'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		if ( !( $userId = $this->app->getSingleton('session')->get('id') ) ) {
			header('Location: ' . $this->app->getRootPath() . 'signin');

			exit;
		}

		$this->app->getSingleton('learn')->learn($userId);
	}

	/**
	 * Get personal items
	 */
	public function items()
	{
		if ( !( $userId = $this->app->getSingleton('session')->get('id') ) ) {
			header('HTTP/1.0 403 Forbidden');

			exit;
		}

		require_once 'HTMLPurifier/Bootstrap.php';

		$this->view->name = 'feed-items';

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
				users_items.read != 1 OR users_items.read IS NULL
      ORDER BY score DESC, items.posted_at ASC
			LIMIT 500
			;');

		$sth->bindParam('user_id', $userId);

		$sth->execute();

		$result = $sth->fetchAll(\PDO::FETCH_OBJ);

		$items = $result;

		foreach ( $items as $item ) {
			$item->contents = $this->_clean($item->contents);
		}

		$this->view->set('items', $items);
	}

	/**
	 * Register vote
	 */
	public function vote()
	{
		header('Content-type: application/json');

		if ( !( $userId = $this->app->getSingleton('session')->get('id') ) ) {
			header('HTTP/1.0 403 Forbidden');

			exit(json_encode(array(
				'error' => 'You need to be logged in to vote'
				)));
		}

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

		if ( !( $userId = $this->app->getSingleton('session')->get('id') ) ) {
			header('HTTP/1.0 403 Forbidden');

			exit(json_encode(array(
				'error' => 'You need to be logged in to mark items as read'
				)));
		}

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
	 * Sanitise HTML
	 *
	 * @param string $html
	 * @return string
	 */
	private function _clean($html)
	{
		// Remove FeedBurner cruft
		$html = preg_replace('/(<div class="feedflare.+?<\/div>|<img[^>]+?(feedsportal|feedburner)\.com[^>]+?>)/s', '', $html);

		$config = \HTMLPurifier_Config::createDefault();

		$config->set('HTML.Allowed', 'h1,h2,h3,h4,h5,h6,a[href],p,ul,ol,li,blockquote,em,i,strong,b,img[src],pre,code,table,thead,tbody,tfoot,tr,th,td');
		$config->set('AutoFormat.AutoParagraph', true);
		$config->set('AutoFormat.RemoveEmpty', true);
		$config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
		$config->set('HTML.SafeObject', true);
		$config->set('Output.FlashCompat', true);

		$purifier = new \HTMLPurifier($config);

		$html = $purifier->purify($html);

		$html = preg_replace('/<table>/', '<table class="table table-bordered table-striped table-hover">', $html);

		return $html;
	}
}
