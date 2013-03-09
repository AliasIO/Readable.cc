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
		if ( !$this->app->getSingleton('session')->get('id') ) {
			header('Location: ' . $this->app->getRootPath() . 'signin');

			exit;
		}
	}

	public function ajax()
	{
		if ( !( $userId = $this->app->getSingleton('session')->get('id') ) ) {
			header('HTTP/1.0 403 Forbidden');

			exit;
		}

		$this->view->name = 'personal-ajax';

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			SELECT
				users_feeds.name AS feed_name,
				items.url,
				items.title,
				items.contents,
				items.posted_at
			FROM      users_feeds
			LEFT JOIN items       ON users_feeds.feed_id = items.feed_id
			WHERE
				users_feeds.user_id = :user_id
			ORDER BY items.posted_at DESC
			LIMIT 1000
			;');

		$sth->bindParam('user_id', $userId);

		$sth->execute();

		$result = $sth->fetchAll(\PDO::FETCH_OBJ);

		$this->view->set('items', $result);
	}
}
