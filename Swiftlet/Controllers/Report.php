<?php

namespace Swiftlet\Controllers;

class Report extends \Swiftlet\Controller
{
	protected
		$title = 'Report'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$this->article();
	}

	public function article()
	{
		$email  = isset($_POST['email'])  ? $_POST['email']  : '';
		$reason = isset($_POST['reason']) ? $_POST['reason'] : '';

		$item = false;

		$args = $this->app->getArgs();

		if ( !empty($args[0]) ) {
			$itemId = $args[0];

			$this->view->set('itemId', $itemId);

			$dbh = $this->app->getSingleton('pdo')->getHandle();

			$sth = $dbh->prepare('
				SELECT
					feeds.id    AS feed_id,
					feeds.title AS feed_title,
					items.title,
					items.url
				FROM      items
				LEFT JOIN feeds ON feeds.id = items.feed_id
				WHERE
					items.id = :id
				LIMIT 1
				;');

			$sth->bindParam(':id', $itemId);

			$sth->execute();

			$item = $sth->fetch(\PDO::FETCH_OBJ);
		}

		if ( !$item ) {
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');

			$this->view->set('pageTitle', 'Error 404');

			$this->view->name = 'error404';
		} else {
			if ( !empty($_POST) ) {
				$message =
					"Inappropriate content has been reported:\n\n" .
					"\tItem ID" . $item->id . "\n" .
					"\t" . $item->url . "\n\n" .
					"\tFeed ID" . $item->feed_id . "\n" .
					"\t" . $item->feed_title . "\n" .
					"\t" . $this->app->getConfig('websiteUrl') . "/feed/view/" . $item->feed_id . "\n\n" .
					"Reason:\n\n" .
					"\t" . $reason . "\n\n" .
					"Email address:\n\n" .
					"\t" . $email . "\n\n" .
					"User ID:\n\n" .
					"\t" . $this->app->getSingleton('session')->get('id') . "\n\n" .
					"User email address:\n\n" .
					"\t" . $this->app->getSingleton('session')->get('email')
					;

				$this->app->getSingleton('helper')->sendMail($this->app->getConfig('emailFrom'), 'Inappropriate content report', $message);

				$this->view->set('success', 'Thank you, your report has been sent.');
			}
		}
	}
}
