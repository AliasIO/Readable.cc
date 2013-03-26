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
			  	users.enabled                 = 1                                                                                       AND -- Fetch feed for enabled users
			  	users.last_active_at          > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY)                                              AND -- Fetch feed for active users
			  ( feeds.last_fetch_attempted_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL  6 HOUR) OR  feeds.last_fetch_attempted_at IS NULL ) AND -- Fetch feeds four times a day
				( feeds.last_fetched_at         > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 90 DAY)  OR  feeds.last_fetched_at         IS NULL )     -- Give up on feeds after three months of failed attempts
			GROUP BY feeds.id
			LIMIT 50
			;');

		$sth->execute();

		$results = $sth->fetchAll(\PDO::FETCH_OBJ);

		echo 'Fetching ' . count($results) . ' feeds&hellip;<br>';

		foreach ( $results as $result ) {
			$feed = $this->app->getModel('feed');

			try {
				$feed->fetch($result->url, false);
			} catch ( \Exception $e ) {
				echo $result->url . ': (' . $e->getCode() . ') ' . $e->getMessage() . '<br>';

				continue;
			}

			$feed->id = $result->id;

			$feed->saveItems();
		}

		// Learning
		$sth = $dbh->prepare('
			SELECT
				users.id
			FROM users
			WHERE
			  	enabled         = 1                                                                      AND -- Learn only for enabled users
				( last_learned_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY) OR last_learned_at IS NULL ) AND -- Learn once a day at most
			    last_active_at  > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY)                                  -- Learn only for recently active users
			LIMIT 1000
			;');

		$sth->execute();

		$users = $sth->fetchAll(\PDO::FETCH_OBJ);

		echo 'Learning for ' . count($users) . ' users&hellip;<br>';

		foreach ( $users as $user ) {
			$this->app->getSingleton('learn')->learn($user->id);
		}

		// Prune sessions
		if ( $handle = opendir('sessions') ) {
			while ( ( $file = readdir($handle) ) !== FALSE ) {
				if ( is_file('sessions/' . $file) ) {
					$parts = explode('_', $file);

					$expiry = array_shift($parts);

					if ( $expiry < time() ) {
						try {
							unlink('sessions/' . $file);
						} catch ( \Exception $e ) {
						}
					}
				}
			}

			closedir($handle);
		}

		exit('Done.');
	}
}
