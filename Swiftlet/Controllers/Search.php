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
		$args = $this->app->getArgs();

		$query = !empty($args[0]) ? $args[0] : '';

		$this->view->set('query', $query);

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
					0                AS vote,
					0                AS saved,
					0                AS score,
					0                AS feed_subscribed
				FROM (
					SELECT
						main.*,
						COUNT(id) AS matches
					FROM (
						SELECT
							items.id,
							items.posted_at
						FROM (
							SELECT
								id
							FROM words
							WHERE
								words.word IN ( ' . implode(', ', array_fill(0, count($words), '?')) . ' )
							LIMIT 10                                                     -- Search 10 words at most
						) AS main
						INNER JOIN items_words ON items_words.word_id =        main.id
						INNER JOIN items       ON       items.id      = items_words.item_id
						WHERE
							items.posted_at > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY) -- Search items no more than a month old
						ORDER BY DATE(items.posted_at) DESC
						LIMIT 1000                                                     -- Return at most the last 1000 matching items
					) AS main
					GROUP BY id
					ORDER BY matches DESC, DATE(main.posted_at) DESC
					LIMIT ?, ?
				) AS main
				INNER JOIN items ON items.id = main.id
				INNER JOIN feeds ON feeds.id = items.feed_id
				');

			$i = 1;

			foreach( $words as $key => $word ) {
				$sth->bindParam($i ++, $words[$key], \PDO::PARAM_INT);
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
