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

		require_once 'HTMLPurifier/Bootstrap.php';

		$this->view->name = 'personal-ajax';

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			SELECT
				users_feeds.name AS feed_name,
				items.id,
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

		$items = $result;

		foreach ( $items as $item ) {
			$item->contents = $this->_clean($item->contents);
		}

		$this->view->set('items', $items);
	}

	/**
	 * TODO
	 */
	private function _clean($html)
	{
		$config = \HTMLPurifier_Config::createDefault();

		$config->set('HTML.Allowed', 'h1,h2,h3,h4,h5,h6,a[href],p,em,strong,img[src],code,br');
		$config->set('HTML.SafeObject', true);
		$config->set('Output.FlashCompat', true);

		$purifier = new \HTMLPurifier($config);

		return $purifier->purify($html);
	}
}
