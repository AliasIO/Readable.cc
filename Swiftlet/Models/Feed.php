<?php

namespace Swiftlet\Models;

class Feed extends \Swiftlet\Model
{
	const
		FEED_INVALID = 1,
		SERVER_ERROR = 2,
		XSD_RSS      = 'rss-2.0.xsd',
		XSD_ATOM     = 'atom.xsd'
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
		$items = array()
		;

	/**
	 * Fetch a URL using cURL
	 *
	 * @param string $url
	 */
	public function fetch($url)
	{
		$response = $this->curl($url);

		$this->type = $this->validate($response->body);

		// Not a valid feed, perhaps the page is HTML. Find linked feeds.
		if ( !$this->type ) {
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

			if ( !$this->type ) {
				throw new \Exception('Invalid feed', self::FEED_INVALID);
			}
		}

		$this->url = $response->url;

		$this->xml = new \SimpleXMLElement($response->body);

		// Get feed details
		switch ( $this->type ) {
			case 'rss':
				$this->title = (string) $this->xml->channel->title;
				$this->link  = (string) $this->xml->channel->link;

				break;
		}
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

		$dom = new \DOMDocument();

		$dom->loadXml($xml);

		return $dom->schemaValidate(self::XSD_RSS) ? 'rss' : ( $dom->schemaValidate(self::XSD_ATOM) ? 'atom' : '' );
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
		if ( $this->items ) {
			return $this->items;
		}

		switch ( $this->type ) {
			case 'rss':
				foreach ( $this->xml->channel->item as $xml ) {
					$item = $this->app->getModel('feedItem')->init($this, $xml);

					$this->items[] = $item;
				}
		}

		return $this->items;
	}

	/**
	 * Save feed items
	 *
	 * return mixed
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
