<?php

namespace Swiftlet\Controllers;

class Search extends \Swiftlet\Controllers\Read
{
	protected
		$title = 'Search'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$this->getItems();
	}

	/**
	 * Get items
	 */
	public function items()
	{
		if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			$this->view->name = 'read';
		}

		$this->getItems();
	}

	/**
	 * query
	 */
	public function query()
	{
		$this->items();
	}

	/**
	 * Get items
	 */
	protected function getItems()
	{
		$feedIds = array();

		if ( $userId = $this->app->getSingleton('session')->get('id') ) {
			$dbh = $this->app->getSingleton('pdo')->getHandle();

			$sth = $dbh->prepare('
				SELECT
					feeds.id,
					feeds.title
				FROM       users_feeds
				STRAIGHT_JOIN feeds       ON users_feeds.feed_id = feeds.id
				WHERE
					users_feeds.user_id = :user_id
				ORDER BY feeds.title
				LIMIT 10000
				;');

			$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

			$sth->execute();

			$feeds = $sth->fetchAll(\PDO::FETCH_OBJ);

			foreach ( $feeds as $feed ) {
				$this->app->getSingleton('helper')->localize($feed->last_fetched_at);
			}

			$this->view->set('feeds', $feeds);

			foreach ( $feeds as $feed ) {
				$feedIds[] = $feed->id;
			}
		}

		$args = $this->app->getArgs();

		$query   = !empty($args[0]) ? $args[0] : '';
		$feedIds = !empty($args[1]) ? ( $args[1] == 'my' ? $feedIds : array((int) $args[1]) ) : array();

		$this->view->set('query', $query);
		$this->view->set('feed',  count($feedIds) > 1 ? 'my' : ( count($feedIds) == 1 ? $feedIds[0] : '' ));

		$words = $this->app->getSingleton('helper')->extractWords($query);

		if ( $words ) {
			$page = !empty($_GET['page']) ? (int) abs($_GET['page']) : 1;

			$dbh = $this->app->getSingleton('pdo')->getHandle();

			$sth = $dbh->prepare('
				SELECT
					feeds.id         AS feed_id,
					feeds.title      AS feed_title,
					feeds.link       AS feed_link,
					items.id,
					items.feed_id,
					items.url,
					items.title,
					items.contents,
					items.posted_at,
					NULL             AS folder_id,
					0                AS starred,
					0                AS feed_subscribed
				FROM (
					SELECT
						main.*,
						COUNT(id) AS matches
					FROM (
						SELECT
							items.id,
							IF(items.posted_at, items.posted_at, items.created_at) AS posted_at
						FROM (
							SELECT
								id
							FROM words
							WHERE
								words.word IN ( ' . implode(', ', array_fill(0, count($words), '?')) . ' )
							GROUP BY words.id
							LIMIT 10                                                     -- Search 10 words at most
						) AS main
						STRAIGHT_JOIN items_words ON items_words.word_id =        main.id
						STRAIGHT_JOIN items       ON       items.id      = items_words.item_id
						STRAIGHT_JOIN feeds       ON       feeds.id      =       items.feed_id' . ( $feedIds ? ' AND feeds.id IN ( ' . implode(', ', array_fill(0, count($feedIds), '?')) . ' ) ' : '' ) . '
						WHERE
							items.posted_at > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY) AND -- Search items no more than a month old
							items.posted_at < UTC_TIMESTAMP()                                -- Do not return future dated items
						ORDER BY items.posted_at DESC
						LIMIT 1000                                                         -- Return at most the last 1000 matching items
					) AS main
					GROUP BY id
					ORDER BY matches DESC, main.posted_at DESC
					LIMIT ?, ?
				) AS main
				STRAIGHT_JOIN items ON items.id = main.id
				STRAIGHT_JOIN feeds ON feeds.id = items.feed_id
				');

			$i = 1;

			foreach( $words as $key => $word ) {
				$sth->bindParam($i ++, $words[$key], \PDO::PARAM_INT);
			}

			foreach( $feedIds as $key => $feedId ) {
				$sth->bindParam($i ++, $feedIds[$key], \PDO::PARAM_INT);
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
}
