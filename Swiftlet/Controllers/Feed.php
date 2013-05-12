<?php

namespace Swiftlet\Controllers;

class Feed extends \Swiftlet\Controllers\Read
{
	protected
		$title = 'Feed'
		;

	/**
	 * Default action
	 */
	public function view()
	{
		$this->getItems();
	}

	/**
	 * Get personal items
	 */
	public function items()
	{
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

		$excludes = !empty($_GET['excludes']) ? explode(' ', $_GET['excludes']) : array();
		$page     = !empty($_GET['page'])     ? (int)        abs($_GET['page']) : 1;

		$args = $this->app->getArgs();

		$feedId = !empty($args[0]) ? $args[0] : null;

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			SELECT
				title,
				link
			FROM feeds
			WHERE
				feeds.id = :feed_id
			LIMIT 1
			');

		$sth->bindParam('feed_id', $feedId, \PDO::PARAM_INT);

		$sth->execute();

		$feed = $sth->fetch(\PDO::FETCH_OBJ);

		if ( !$feed ) {
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');

			$this->view->set('pageTitle', 'Error 404');

			$this->view->name = 'error404';
		} else {
			$this->view->set('pageTitle',       $feed->title);
			$this->view->set('pageDescription', 'News from ' . $feed->title . ' at ' . parse_url($feed->link, PHP_URL_HOST) . '.');
			$this->view->set('title',           $feed->title);
			$this->view->set('link',            $feed->link);
			$this->view->set('canonicalUrl',    $this->app->getConfig('websiteUrl') . $this->app->getSingleton('helper')->getFeedLink($feedId, $feed->title));

			/*
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
					0                AS saved,
					COALESCE(AVG(users_items.score), 0) AS score
				FROM             items
				INNER JOIN       feeds ON       feeds.id      = items.feed_id
				LEFT  JOIN users_items ON users_items.item_id = items.id
				WHERE
					feeds.id = ?
					' . ( $excludes ? 'AND items.id NOT IN ( ' . implode(', ', array_fill(0, count($excludes), '?')) . ' )' : '' ) . '
				GROUP BY items.id
				ORDER BY DATE(items.posted_at) DESC, AVG(users_items.score) DESC
				';

			if ( $userId ) {
				$select = '
					SELECT
						main.*,
						COALESCE(users_items.vote,  0)   AS vote,
						COALESCE(users_items.saved, 0)   AS saved,
						IF(users_feeds.id IS NULL, 0, 1) AS feed_subscribed
					FROM ( ' . $select . ' ) AS main
					LEFT JOIN users_items ON users_items.item_id = main.id      AND users_items.user_id = ?
					LEFT JOIN users_feeds ON users_feeds.feed_id = main.feed_id AND users_feeds.user_id = ?
					WHERE
						users_items.read != 1 OR users_items.read IS NULL
					';
			}

			$select .= ' LIMIT ' . ( ( $page - 1 ) * self::ITEMS_PER_PAGE ) . ', ' . ( $page * self::ITEMS_PER_PAGE );

			$sth = $dbh->prepare($select);

			$i = 1;

			$sth->bindParam($i ++, $feedId);

			foreach( $excludes as $key => $itemId ) {
				$sth->bindParam($i ++, $excludes[$key]);
			}

			if ( $userId ) {
				$sth->bindParam($i ++, $userId);
				$sth->bindParam($i ++, $userId);
			}
			*/

			$sth = $dbh->prepare('
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
					0                AS saved,
					COALESCE(AVG(users_items.score), 0) AS score
				FROM             items
				INNER JOIN       feeds ON       feeds.id      = items.feed_id
				LEFT  JOIN users_items ON users_items.item_id = items.id
				WHERE
					feeds.id = :feed_id
				GROUP BY items.id
				ORDER BY DATE(items.posted_at) DESC, AVG(users_items.score) DESC
				LIMIT :limit_from, :limit_count
				');

			$limitFrom  = ( $page - 1 ) * self::ITEMS_PER_PAGE;
			$limitCount = self::ITEMS_PER_PAGE;

			$sth->bindParam('limit_from',  $limitFrom,  \PDO::PARAM_INT);
			$sth->bindParam('limit_count', $limitCount, \PDO::PARAM_INT);
			$sth->bindParam('feed_id',     $feedId,     \PDO::PARAM_INT);

			$sth->execute();

			$result = $sth->fetchAll(\PDO::FETCH_OBJ);

			$items = $result;

			$this->prepare($items);

			$this->view->set('items', $items);
		}
	}
}
