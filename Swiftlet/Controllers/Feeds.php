<?php

namespace Swiftlet\Controllers;

class Feeds extends \Swiftlet\Controller
{
	protected
		$title = 'Manage feeds'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$userId = $this->app->getSingleton('helper')->ensureValidUser();

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$success = false;
		$error   = false;

		if ( !empty($_POST['form']) ) {
			if ( $_POST['form'] == 'new' ) {
				$url = !empty($_POST['url']) ? $_POST['url'] : '';

				if ( !$url ) {
					$error = 'Please provide a URL.';
				} else {
					$feed = $this->app->getModel('feed');

					try {
						$feed->fetch($url);
					} catch ( \Exception $e ) {
						switch ( $e->getCode() ) {
							case $feed::FEED_INVALID:
								$error = 'No valid feed found at the specified URL.';

								break;
							case $feed::SERVER_ERROR:
							default:
								$error = 'Failed to fetch the feed at the specified URL, please try again.';

								break;
						}
					}

					if ( !$error ) {
						$url   = $feed->getUrl();
						$title = $feed->getTitle();
						$link  = $feed->getLink();

						// Add the feed
						$sth = $dbh->prepare('
							INSERT IGNORE INTO feeds (
								url,
								title,
								link,
								created_at
							) VALUES (
								:url,
								:title,
								:link,
								UTC_TIMESTAMP()
							)
							;');

						$sth->bindParam('url',   $url);
						$sth->bindParam('title', $title);
						$sth->bindParam('link',  $link);

						$result = $sth->execute();

						$feedId = $dbh->lastInsertId();

						// Nothing was inserted, feed may already exist
						if ( !$feedId ) {
							$sth = $dbh->prepare('
								SELECT
									id
								FROM feeds
								WHERE
									url = :url
								LIMIT 1
								;');

							$sth->bindParam('url', $url);

							$sth->execute();

							$result = $sth->fetch(\PDO::FETCH_OBJ);

							if ( $result ) {
								$feedId = $result->id;
							}
						}

						// Cross reference feed and user
						if ( $feedId ) {
							$sth = $dbh->prepare('
								INSERT IGNORE INTO users_feeds (
									user_id,
									feed_id
								) VALUES (
									:user_id,
									:feed_id
								)
								;');

							$sth->bindParam('user_id', $userId);
							$sth->bindParam('feed_id', $feedId);

							$sth->execute();
						}
					}
				}

				if ( $error ) {
					$this->view->set('error', $error);

					$this->view->set('url',  $url);

					$this->view->set('error-new', true);
				} else {
					$this->view->set('success', 'The feed has been added.');
				}
			}
		}

		$sth = $dbh->prepare('
			SELECT
				feeds.id,
				feeds.url,
				feeds.title,
				feeds.link
			FROM      users_feeds
			LEFT JOIN feeds ON users_feeds.feed_id = feeds.id
			WHERE
				users_feeds.user_id = :user_id
			ORDER BY users_feeds.id DESC
			LIMIT 1000
			;');

		$sth->bindParam('user_id', $userId);

		$sth->execute();

		$result = $sth->fetchAll(\PDO::FETCH_OBJ);

		$this->view->set('feeds', $result);
	}

	/**
	 * Delete feeds
	 */
	public function remove()
	{
		$userId = $this->app->getSingleton('helper')->ensureValidUser(true);

		if ( !empty($_POST['id']) ) {
			$feedId = $_POST['id'];

			$dbh = $this->app->getSingleton('pdo')->getHandle();

			$sth = $dbh->prepare('
				DELETE
				FROM users_feeds
				WHERE
					user_id = :user_id AND
					feed_id = :feed_id
				LIMIT 1
				;');

			$sth->bindParam('user_id', $userId);
			$sth->bindParam('feed_id', $feedId);

			$sth->execute();
		}

		exit;
	}
}
