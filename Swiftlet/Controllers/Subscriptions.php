<?php

namespace Swiftlet\Controllers;

class Subscriptions extends \Swiftlet\Controller
{
	const
		OPML_INVALID = 1,
		XSD_OPML     = 'opml-2.0.xsd'
		;

	protected
		$title = 'Manage subscriptions'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$userId = $this->app->getSingleton('helper')->ensureValidUser();

		if ( !empty($_POST['form']) ) {
			switch ( $_POST['form'] ) {
				case 'subscribe':
					$this->subscribe();

					break;
				case 'import':
					$this->import();

					break;
			}
		}

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		// Get currently subscribed feeds
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
	 * Add a new feed
	 */
	protected function subscribe()
	{
		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$success = false;
		$error   = false;

		$url = !empty($_POST['url']) ? $_POST['url'] : '';

		if ( !$url ) {
			$error = 'Please provide a URL.';
		} else {
			$feed = $this->app->getModel('feed');

			try {
				$feed->fetch($url)->save();
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
		}

		if ( $error ) {
			$this->view->set('error', $error);

			$this->view->set('url',  $url);

			$this->view->set('error-url', true);
		} else {
			$this->view->set('success', 'The feed has been added.');
		}
	}
	/**
	 * Add a new feed
	 */
	protected function import()
	{
		$success = false;
		$error   = false;

		if ( !empty($_FILES['file']) )
		{
			if ( $_FILES['file']['error'] == UPLOAD_ERR_OK ) {
				$file = $_FILES['file']['tmp_name'];

				// Validate OPML
				libxml_use_internal_errors(true);

				$dom = new \DOMDocument();

				$dom->load($file);

				if ( !$dom->schemaValidate(self::XSD_OPML) ) {
					$error = 'The OPML file appears to be invalid.';
				} else {
					$xml = new \SimpleXMLElement(file_get_contents($file));

					$feeds = array();

					foreach ( $xml->body->outline as $outline ) {
						foreach ( $outline as $xml ) {
							$url   = $xml->attributes()->xmlUrl;
							$title = $xml->attributes()->title;
							$link  = $xml->attributes()->htmlUrl;

							$feed = $this->app->getModel('feed')->dummy($title, $url, $link);

							$feed->save();
						}
					}
				}
			} else {
				switch ( $_FILES['file']['error'] ) {
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$error = 'The uploaded file is too large.';

						break;
					case UPLOAD_ERR_NO_TMP_DIR:
					case UPLOAD_ERR_CANT_WRITE:
					case UPLOAD_ERR_EXTENSION:
						$error = 'Upload failed due to a configuration error.';

						break;

					case UPLOAD_ERR_PARTIAL:
					case UPLOAD_ERR_NO_FILE:
					default:
						$error = 'File upload failed, please try again.';
				}
			}
		}

		if ( $error ) {
			$this->view->set('error', $error);

			$this->view->set('error-file', true);
		} else {
			$this->view->set('success', 'Subscriptions have been added successfully.');
		}
	}

	/**
	 * Delete feeds
	 */
	public function unsubscribe()
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
