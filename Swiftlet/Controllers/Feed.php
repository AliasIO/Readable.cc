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
	 * Get feed items
	 */
	public function items()
	{
		if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			$this->view->name = 'read';
		}

		$this->getItems();
	}

	/**
	 * Get feed items
	 */
	protected function getItems()
	{
		$page = !empty($_GET['page']) ? max(1, (int) abs($_GET['page'])) : 1;

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

			// Redirect to full URL
			$feedLink = $this->app->getSingleton('helper')->getFeedLink($feedId, $feed->title);

			$args = $this->app->getArgs();

			if ( $feedLink != '/feed/view/' . $feedId . '/' && ( empty($args[1]) || '/feed/view/' . $feedId . '/' . $args[1] !== $feedLink ) ) {
				header('HTTP/1.1 301 Moved Permanently');
				header('Status: 301 Moved Permanently');
				header('Location: ' . $feedLink);

				exit;
			}

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
					NULL             AS folder_id,
					0                AS starred,
					0                AS feed_subscribed
				FROM       feeds
				STRAIGHT_JOIN items ON items.feed_id = feeds.id
				WHERE
					feeds.id = :feed_id
				GROUP BY items.id
				ORDER BY DATE(IF(items.posted_at, items.posted_at, items.created_at)) DESC
				LIMIT :limit_from, :limit_count
				');

			$limitFrom  = ( $page - 1 ) * self::ITEMS_PER_PAGE;
			$limitCount = self::ITEMS_PER_PAGE;

			$sth->bindParam('feed_id',     $feedId,     \PDO::PARAM_INT);
			$sth->bindParam('limit_from',  $limitFrom,  \PDO::PARAM_INT);
			$sth->bindParam('limit_count', $limitCount, \PDO::PARAM_INT);

			$sth->execute();

			$result = $sth->fetchAll(\PDO::FETCH_OBJ);

			$items = $result;

			$this->prepare($items);

			$this->view->set('items', $items);
		}
	}
}
