<?php

namespace Swiftlet\Controllers;

class Sitemap extends \Swiftlet\Controllers\Read
{
	protected
		$title = 'Sitemap'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		header('Content-type: application/xml');

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			SELECT
				id
			FROM feeds
			;');

		$sth->execute();

		$feeds = $sth->fetchAll(\PDO::FETCH_OBJ);

		$this->view->set('feeds', $feeds);
	}
}
