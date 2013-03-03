<?php

namespace Swiftlet\Controllers;

class Feeds extends \Swiftlet\Controller
{
	protected
		$title = 'Manage RSS feeds'
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

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			SELECT
				users_feeds.name,
				feeds.url
			FROM      users_feeds
			LEFT JOIN feeds       WHERE users_feeds.feed_id = feeds.id
			WHERE
				users_feed.user_id = :user_id
			ORDER BY users_feeds.id DESC
			LIMIT 1000
			;');

		$sth->bindParam('user_id', $userId);

		$result = $sth->fetch();

		$this->view->set('feeds', $result);
	}
}
