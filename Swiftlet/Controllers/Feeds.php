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

		$name = !empty($_POST['name']['new']) ? $_POST['name']['new'] : '';
		$url  = !empty($_POST['url' ]['new']) ? $_POST['url' ]['new'] : '';

		if ( !empty($_POST) ) {
			$success = false;
			$error   = false;

			if ( $name || $url ) {
				if ( !$url ) {
					$error = 'Please provide a URL.';
				} else {
					try {
						$result = $this->_fetch($url);

						// Find RSS feed URL in an HTML page
						preg_match('/<link[^>]+rel=("|\')alternate\1[^>]*>/is', $result->contents, $match);

						if ( !empty($match) && preg_match('/type=("|\').*?rss.*?\1/is', $match[0]) ) {
							preg_match('/href=("|\')(.+?)\1/i', $match[0], $match);

							if ( isset($match[2]) ) {
								$url = $match[2];

								$result = $this->_fetch($url);
							}
						}

						if ( !$this->_isRss($result->contents) ) {
							$error = 'No valid RSS feed found at the specified URL.';
						}
					} catch ( \Exception $e ) {
						$error = 'The feed at the specified URL could not be fetched, please try again.';
					}

					if ( !$error ) {
						$url = $result->url;

						// Add the feed
						$sth = $dbh->prepare('
							INSERT IGNORE INTO feeds (
								url,
								created_at,
								last_fetched_at,
								last_read_at
							) VALUES (
								:url,
								NOW(),
								NOW(),
								NOW()
							)
							;');

						$sth->bindParam('url', $url);

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
									feed_id,
									name
								) VALUES (
									:user_id,
									:feed_id,
									:name
								)
								;');

							$sth->bindParam('user_id', $userId);
							$sth->bindParam('feed_id', $feedId);
							$sth->bindParam('name',    $name);

							$result = $sth->execute();

							if ( $result ) {
								$success = 'Feed added!';
							}
						}

						if ( !$success ) {
							$error = 'Something went wrong, please try again.';
						}
					}
				}
			}

			if ( $success ) {
				$this->view->set('success', $success);
			} else {
				$this->view->set('error', $error);

				$this->view->set('name-new', $name);
				$this->view->set('url-new',  $url);
			}
		}

		$sth = $dbh->prepare('
			SELECT
				users_feeds.id,
				users_feeds.name,
				feeds.url
			FROM      users_feeds
			LEFT JOIN feeds       ON users_feeds.feed_id = feeds.id
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
	 * Fetch a URL using cURL
	 *
	 * @param string $url
	 * @return object
	 */
	private function _fetch($url)
	{
		$ch = curl_init($url);

		curl_setopt_array($ch, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HEADER         => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS      => 3,
			CURLOPT_TIMEOUT        => 3,
			CURLOPT_USERAGENT      => 'http://readable.cc'
			));

		$response = curl_exec($ch);

		if ( curl_errno($ch) !== 0 ) {
			throw new \Exception(curl_error($ch));
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ( $httpCode != 200 ) {
			throw new \Exception('cURL request returned HTTP code ' . $httpCode);
		}

		$result = new \stdClass();

		$result->url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

		$result->contents = substr($response, $headerSize);

		return $result;
	}

	/**
	 * Test if a string is a valid RSS feed
	 *
	 * @param string $xml
	 * @return bool
	 */
	private function _isRss($xml)
 	{
		try {
			$xml = new \SimpleXMLElement($xml);

			return $xml->getName() == 'rss';
		} catch ( \Exception $e ) { }
	}
}
