<?php

namespace Swiftlet\Controllers;

class Subscriptions extends \Swiftlet\Controller
{
	const
		OPML_INVALID = 1
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
				case 'folders':
					$this->folders();

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
				feeds.link,
				feeds.last_fetched_at,
				users_feeds.folder_id
			FROM       users_feeds
			INNER JOIN feeds       ON users_feeds.feed_id = feeds.id
			WHERE
				users_feeds.user_id = :user_id
			ORDER BY feeds.title
			LIMIT 10000
			;');

		$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

		$sth->execute();

		$feeds = $sth->fetchAll(\PDO::FETCH_OBJ);

		foreach ( $feeds as $feed ) {
			$this->app->getSingleton('helper')->localize($feed->last_fetched_at);
		}

		$this->view->set('feeds',   $feeds);
		$this->view->set('folders', $this->app->getSingleton('helper')->getUserFolders());
		$this->view->set('paid',    $this->app->getSingleton('helper')->userPaid());
	}

	/**
	 * Add a new feed
	 *
	 * @throws \Swiftlet\Exception
	 */
	public function subscribe()
	{
		header('Content-type: application/json');

		$this->app->getSingleton('helper')->ensureValidUser();

		$success = false;
		$error   = false;

		$id       = !empty($_POST['id'])       ? (int) $_POST['id']       : null;
		$folderId = !empty($_POST['folderId']) ? (int) $_POST['folderId'] : null;
		$url      = !empty($_POST['url'])      ?       $_POST['url']      : null;

		if ( !$id && !$url ) {
			$error = 'Please provide a URL.';
		} else {
			try {
				$id = $this->app->getSingleton('subscription')->subscribe($id, $url, $folderId);
			} catch ( \Swiftlet\Exception $e ) {
				switch ( $e->getCode() ) {
					case \Swiftlet\Models\Feed::FEED_INVALID:
						$error = 'The feed appears to be invalid and could not be added.';

						break;
					case \Swiftlet\Models\Feed::NOT_FOUND:
						$error = 'No feed found.';

						break;
					case \Swiftlet\Models\Feed::TIMEOUT:
						$error = 'The website took too long to respond, please try again.';

						break;
					case \Swiftlet\Models\Feed::CURL_ERROR:
					default:
						$error = 'The feed could not be fetched';

						break;
					case \Swiftlet\Models\Feed::SERVER_ERROR:
					default:
						$error = 'The feed could not be fetched, the website returned an error.';

						break;
				}
			}
		}

		if ( $error ) {
			header('HTTP/1.0 503 Service Unavailable');

			exit(json_encode(array('message' => $error)));
		} else {
			exit(json_encode(array('feed_id' => $id)));
		}
	}

	/**
	 * Delete feeds
	 */
	public function unsubscribe()
	{
		header('Content-type: application/json');

		$this->app->getSingleton('helper')->ensureValidUser(true);

		$id  = !empty($_POST['id'])  ? (int) $_POST['id']  : null;
		$url = !empty($_POST['url']) ?       $_POST['url'] : null;

		$this->app->getSingleton('subscription')->unsubscribe($id, $url);

		exit(json_encode(array()));
	}

	/**
	 * Add feed to folder
	 */
	public function folder()
	{
		header('Content-type: application/json');

		$this->app->getSingleton('helper')->ensureValidUser(true);

		$id       = !empty($_POST['id'])       ? (int) $_POST['id']       : null;
		$folderId = !empty($_POST['folderId']) ? (int) $_POST['folderId'] : null;

		$this->app->getSingleton('subscription')->folder($id, $folderId);

		exit(json_encode(array()));
	}

	/**
	 * Welcome message
	 */
	public function welcome()
	{
		$this->view->set('success', 'Welcome! Your account has been created and you have been signed in.');

		$this->index();
	}

	/**
	 * Export feeds to OPML
	 */
	public function export()
	{
		header('Content-type: application/xml');
		header('Content-Disposition: attachment; filename="feeds.opml.xml"');

		$userId = $this->app->getSingleton('helper')->ensureValidUser();

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			SELECT
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

		$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

		$sth->execute();

		$feeds = $sth->fetchAll(\PDO::FETCH_OBJ);

		$opml = new \SimpleXMLElement('<opml></opml>');

		$opml->addAttribute('version', '1.0');

		$opml->addChild('head', $this->app->getConfig('websiteName'));

		$body = $opml->addChild('body');

		$outlines = $body->addChild('outline');

		$outlines->addAttribute('title', 'My Reading');

		foreach ( $feeds as $feed ) {
			$outline = $outlines->addChild('outline');

			$outline->addAttribute('title',   $feed->title);
			$outline->addAttribute('xmlUrl',  $feed->url);
			$outline->addAttribute('htmlUrl', $feed->link);
		}

		exit($opml->saveXML());
	}

	/**
	 * Add a new feed
	 */
	protected function import()
	{
		$userId = $this->app->getSingleton('helper')->ensureValidUser();

		$success = false;
		$error   = false;

		if ( !empty($_FILES['file']) )
		{
			if ( $_FILES['file']['error'] == UPLOAD_ERR_OK ) {
				$file = $_FILES['file']['tmp_name'];

				// Validate OPML
				libxml_use_internal_errors(true);

				try {
					$xml = new \SimpleXMLElement(file_get_contents($file));

					if ( !$xml->body->outline ) {
						throw new \Swiftlet\Exception('Invalid OPML');
					}

					$feeds = array();

					$folders = $this->app->getSingleton('helper')->getUserFolders();

					foreach ( $xml->body->outline as $outline ) {
						$folderId = null;

						if ( !$outline->outline ) {
							$outline = array($outline);
						} else {
							$folderTitle = $outline->attributes()->title ? $outline->attributes()->title : ( $outline->attributes()->text ? $outline->attributes()->text : '' );

							if ( $folderTitle ) {
								foreach ( $folders as $folder ) {
									if ( strtolower($folder->title) == strtolower($folderTitle) ) {
										$folderId = $folder->id;
									}
								}

								// Create a new folder
								if ( !$folderId ) {
									$dbh = $this->app->getSingleton('pdo')->getHandle();

									$sth = $dbh->prepare('
										INSERT INTO folders (
											title,
											user_id
										) VALUES (
											:title,
											:user_id
										)
										');

									$sth->bindParam('title',   $folderTitle);
									$sth->bindParam('user_id', $userId);

									$sth->execute();

									$folderId = $dbh->lastInsertId();
								}
							}
						}

						foreach ( $outline as $xml ) {
							$url   = $xml->attributes()->xmlUrl;
							$title = $xml->attributes()->title;
							$link  = $xml->attributes()->htmlUrl;

							if ( !$url || !$title || !$link ) {
								throw new \Swiftlet\Exception('Invalid OPML');
							}

							$feed = $this->app->getModel('feed')->dummy($title, $url, $link, $folderId);

							$feed->save();
						}
					}
				} catch ( \Swiftlet\Exception $e ) {
					$error = 'The OPML file appears to be invalid.'.$e->getMessage();
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
	 * Save folders
	 */
	protected function folders()
	{
		$userId = $this->app->getSingleton('helper')->ensureValidUser();

		$success = false;
		$error   = false;

		$titles = isset($_POST['titles']) ? $_POST['titles'] : array();
		$delete = isset($_POST['delete']) ? $_POST['delete'] : array();

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		foreach ( $titles as $folderId => $title ) {
			if ( $folderId === 'new' ) {
				break;
			}

			if ( isset($delete[$folderId]) ) {
				$sth = $dbh->prepare('
					UPDATE users_feeds SET
						folder_id = NULL
					WHERE
						folder_id = :folder_id AND
						user_id   = :user_id
					LIMIT 1000
					');

				$sth->bindParam('folder_id', $folderId, \PDO::PARAM_INT);
				$sth->bindParam('user_id',   $userId,   \PDO::PARAM_INT);

				$sth->execute();

				$sth = $dbh->prepare('
					DELETE
					FROM folders
					WHERE
						id      = :id AND
						user_id = :user_id
					LIMIT 1
					');

					$sth->bindParam('id',      $folderId, \PDO::PARAM_INT);
					$sth->bindParam('user_id', $userId,   \PDO::PARAM_INT);

					$sth->execute();
			} else {
				$sth = $dbh->prepare('
					UPDATE folders SET
						title = :title
					WHERE
						id      = :id      AND
						user_id = :user_id
					LIMIT 1
					');

				$sth->bindParam('title',   $title);
				$sth->bindParam('id',      $folderId, \PDO::PARAM_INT);
				$sth->bindParam('user_id', $userId,   \PDO::PARAM_INT);

				$sth->execute();
			}
		}

		if ( !empty($titles['new']) ) {
			$title = $titles['new'];

			$sth = $dbh->prepare('
				INSERT INTO folders (
					title,
					user_id
				) VALUES (
					:title,
					:user_id
				)
				');

			$sth->bindParam('title',   $title);
			$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

			$sth->execute();
		}

		if ( $error ) {
			$this->view->set('error', $error);

			$this->view->set('error-file', true);
		} else {
			$this->view->set('success', 'The folders have been saved.');
		}
	}

}
