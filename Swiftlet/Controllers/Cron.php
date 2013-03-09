<?php

namespace Swiftlet\Controllers;

class Cron extends \Swiftlet\Controller
{
	public function index()
	{
		$dbh = $this->app->getSingleton('pdo')->getHandle();

		// Feeds
		$sth = $dbh->prepare('
			SELECT
				feeds.id,
				feeds.url
			FROM      users
			INNER JOIN users_feeds ON       users.id      = users_feeds.user_id
			INNER JOIN feeds       ON users_feeds.feed_id =       feeds.id
			WHERE
				users.last_active_at  > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY) -- AND
				-- feeds.last_fetched_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL  1 DAY)
			GROUP BY feeds.id
			;');

		$sth->bindParam('url', $url);

		$sth->execute();

		$results = $sth->fetchAll(\PDO::FETCH_OBJ);

		foreach ( $results as $result ) {
			$feed = $this->app->getModel('feed');

			$feed->id = $result->id;

			try {
				$feed->fetch($result->url);
			} catch ( \Exception $e ) {
				echo 'feed: ' . $e->getCode() . ' ' . $e->getMessage() . '<br>';

				break;
			}

			foreach ( $feed->getItems() as $item ) {
				$feedItem = $this->app->getSingleton('feedItem');

				$feedItem->feed = $feed;
				$feedItem->xml  = $item;

				$feedItem->save();
			}
		}
	}
}
