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
					*,
					COUNT(id) AS matches
				FROM (
					SELECT
						feeds.id         AS feed_id,
						feeds.title      AS feed_title,
						feeds.link       AS feed_link,
						items.id,
						items.url,
						items.title,
						items.contents,
						items.posted_at,
						0                AS vote,
						0                AS saved,
						0                AS score,
						0                AS feed_subscribed
					FROM       words
					INNER JOIN items_words ON items_words.word_id =       words.id
					INNER JOIN items       ON       items.id      = items_words.item_id
					INNER JOIN feeds       ON       feeds.id      =       items.feed_id
					WHERE
						words.word IN ( ' . implode(', ', array_fill(0, count($words), '?')) . ' )
					ORDER BY DATE(items.posted_at) DESC
					LIMIT 1000
				) AS main
				GROUP BY id
				ORDER BY matches DESC, DATE(posted_at) DESC
				LIMIT ?, ?
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
