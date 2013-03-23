<?php

namespace Swiftlet\Models;

class Feed extends \Swiftlet\Model
{
	const
		FEED_INVALID      = 1,
		SERVER_ERROR      = 2,
		NAMESPACE_CONTENT = 'http://purl.org/rss/1.0/modules/content/',
		NAMESPACE_DC      = 'http://purl.org/dc/elements/1.1/'
		;

	public
		$id,
		$timeout = 6
		;

	protected
		$url,
		$xml,
		$type,
		$title,
		$link,
		$items = array(),
		$dummy = false
		;

	/**
	 * Fetch a URL using cURL
	 *
	 * @param string $url
	 * @param bool $findLinked
	 */
	public function fetch($url, $findLinked = true)
	{
		// Set last fetch attempt date
		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			UPDATE feeds SET
				last_fetch_attempted_at = UTC_TIMESTAMP()
			WHERE
				url = :url
			LIMIT 1
			;');

		$sth->bindParam('url', $url);

		$sth->execute();

		$response = $this->curl($url);

		$this->type = $this->validate($response->body);

		// Not a valid feed, perhaps the page is HTML. Find linked feeds.
		if ( !$this->type ) {
			if ( $findLinked ) {
				$html = new \DOMDocument();

				$html->loadHtml($response->body);

				$links = $html->getElementsByTagName('link');

				foreach ( $links as $link ) {
					if ( $link->getAttribute('rel') == 'alternate' ) {
						if ( $link->getAttribute('type') == 'application/rss+xml' || $link->getAttribute('type') == 'application/atom+xml' ) {
							$response = $this->curl($link->getAttribute('href'));

							$this->type = $this->validate($response->body);

							if ( $this->type ) {
								break;
							}
						}
					}
				}
			}

			if ( !$this->type ) {
				throw new \Exception('Invalid feed', self::FEED_INVALID);
			}
		}

		$this->url = $response->url;

		$this->xml = new \SimpleXMLElement($response->body);

		// Get feed details
		switch ( $this->type ) {
			case 'rss1':
			case 'rss1-rdf':
			case 'rss2':
				$this->title = (string) $this->xml->channel->title;
				$this->link  = (string) $this->xml->channel->link;

				break;
			case 'atom':
				$this->title = (string) $this->xml->title;
				$this->link  = (string) $this->xml->link->attributes()->href;

				break;
		}

		// Set last fetched date
		$sth = $dbh->prepare('
			UPDATE feeds SET
				last_fetched_at = UTC_TIMESTAMP()
			WHERE
				url = :url
			LIMIT 1
			;');

		$sth->bindParam('url', $url);

		$sth->execute();

		return $this;
	}

	/**
	 * Dummy feed object
	 *
	 * @param string $title
	 * @param string $url
	 * @param string $link
	 * @return object
	 */
	public function dummy($title, $url, $link)
	{
		$this->title = $title;
		$this->url   = $url;
		$this->link  = $link;

		$this->dummy = true;

		return $this;
	}

	/**
	 * Validate XML, determine if it's valid RSS or Atom
	 *
	 * @param object $xml
	 * @return string
	 */
	protected function validate($xml)
	{
		libxml_use_internal_errors(true);

		try {
			$simpleXml = new \SimpleXMLElement($xml);

			if ( $simpleXml->getName() == 'rss' && $simpleXml->channel && $simpleXml->channel->title && $simpleXml->channel->link && $simpleXml->channel->item ) {
				$item = $simpleXml->channel->item[0];

				if ( $item->title && $item->link ) {
					if ( $item->description ) {
						return 'rss2';
					}

					$content = $item->children(self::NAMESPACE_CONTENT);

					if ( $content->encoded ) {
						return 'rss1';
					}
				}

			}

			if ( $simpleXml->getName() == 'feed' && $simpleXml->title && $simpleXml->link && $simpleXml->entry ) {
				$item = $simpleXml->entry[0];

				if ( $item->title && $item->link->attributes()->href && $item->content && $item->published ) {
					return 'atom';
				}
			}

			if ( $simpleXml->getName() == 'RDF' && $simpleXml->channel && $simpleXml->channel->title && $simpleXml->channel->link && $simpleXml->item ) {
				$item = $simpleXml->item[0];

				if ( $item->title && $item->link ) {
					$content = $item->children(self::NAMESPACE_CONTENT);

					if ( $content->encoded ) {
						return 'rss1-rdf';
					}
				}
			}
		} catch ( \Exception $e ) {
		}
	}

	/**
	 * Fetch a page
	 *
	 * @param string $url
	 * @return object
	 */
	protected function curl($url)
	{
		$response = new \stdClass;

		$ch = curl_init($url);

		curl_setopt_array($ch, array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HEADER         => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS      => 3,
			CURLOPT_TIMEOUT        => $this->timeout,
			CURLOPT_USERAGENT      => 'http://readable.cc'
			));

		$result = curl_exec($ch);

		if ( curl_errno($ch) !== 0 ) {
			throw new \Exception(curl_error($ch), self::SERVER_ERROR);
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ( $httpCode != 200 ) {
			throw new \Exception('cURL request returned HTTP code ' . $httpCode, self::SERVER_ERROR);
		}

		$response->url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

		$response->body = substr($result, $headerSize);

		return $response;
	}

	/**
	 * Get feed items
	 *
	 * return mixed
	 */
	public function getItems()
	{
		if ( $this->dummy ) {
			return array();
		}

		if ( $this->items ) {
			return $this->items;
		}

		switch ( $this->type ) {
			case 'rss1':
			case 'rss2':
				foreach ( $this->xml->channel->item as $xml ) {
					$item = $this->app->getModel('feedItem')->init($this, $xml);

					$this->items[] = $item;
				}

				break;
			case 'atom':
				foreach ( $this->xml->entry as $xml ) {
					$item = $this->app->getModel('feedItem')->init($this, $xml);

					$this->items[] = $item;
				}

				break;
			case 'rss1-rdf':
				foreach ( $this->xml->item as $xml ) {
					$item = $this->app->getModel('feedItem')->init($this, $xml);

					$this->items[] = $item;
				}

				break;
		}

		return $this->items;
	}

	/**
	 * Save feed
	 */
	public function save()
	{
		$userId = $this->app->getSingleton('session')->get('id');

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		// Add the feed
		$sth = $dbh->prepare('
			INSERT IGNORE INTO feeds (
				url,
				title,
				link,
				created_at,
				last_fetched_at,
				last_fetch_attempted_at
			) VALUES (
				:url,
				:title,
				:link,
				UTC_TIMESTAMP(),
				' . ( $this->dummy ? 'NULL' : 'UTC_TIMESTAMP()' ) . ',
				' . ( $this->dummy ? 'NULL' : 'UTC_TIMESTAMP()' ) . '
			)
			;');

		$sth->bindParam('url',   $this->url);
		$sth->bindParam('title', $this->title);
		$sth->bindParam('link',  $this->link);

		$result = $sth->execute();

		$this->id = $dbh->lastInsertId();

		// Nothing was inserted, feed may already exist
		if ( !$this->id ) {
			$sth = $dbh->prepare('
				SELECT
					id
				FROM feeds
				WHERE
					url = :url
				LIMIT 1
				;');

			$sth->bindParam('url', $this->url);

			$sth->execute();

			$result = $sth->fetch(\PDO::FETCH_OBJ);

			if ( $result ) {
				$this->id = $result->id;
			}
		}

		// Cross reference feed and user
		if ( $this->id ) {
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
			$sth->bindParam('feed_id', $this->id);

			$sth->execute();
		}

		$this->saveItems();
	}

	/**
	 * Save feed items
	 */
	public function saveItems()
	{
		foreach ( $this->getItems() as $item ) {
			$item->save();
		}
	}

	/**
	 * Get feed title
	 *
	 * return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Get feed link
	 *
	 * return string
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * Get the URL
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Return the feed type
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
}
