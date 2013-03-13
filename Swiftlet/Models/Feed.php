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
		$contents,
		$xml,
		$feedType,
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

		$response = curl_exec($ch);

		if ( curl_errno($ch) !== 0 ) {
			throw new \Exception(curl_error($ch), self::SERVER_ERROR);
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ( $httpCode != 200 ) {
			throw new \Exception('cURL request returned HTTP code ' . $httpCode, self::SERVER_ERROR);
		}

		$this->url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

		$this->contents = substr($response, $headerSize);

		// Validate feed
		libxml_use_internal_errors(true);

		$xml = new \DOMDocument();

		$xml->loadXml($this->contents);

		$this->feedType = $xml->schemaValidate(self::XSD_RSS) ? 'rss' : ( $xml->schemaValidate(self::XSD_ATOM) ? 'atom' : '' );

		if ( !$this->feedType ) {
			throw new \Exception('Invalid feed', self::FEED_INVALID);
		}

		$this->xml = new \SimpleXMLElement($this->contents);

		// Get feed details
		switch ( $this->feedType ) {
			case 'rss':
				$this->title = (string) $this->xml->channel->title;
				$this->link  = (string) $this->xml->channel->link;

				break;
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
	 * Get feed items
	 *
	 * return mixed
	 */
	public function getItems()
	{
		if ( $this->items ) {
			return $this->items;
		}

		switch ( $this->feedType ) {
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
	 * Get the URL
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Get the body of the cURL response
	 *
	 * @return string
	 */
	public function getContents()
	{
		return $this->contents;
	}

	/**
	 * Return XML if the feed is valid
	 *
	 * @return object
	 */
	public function getXml()
	{
		return $this->xml;
	}

	/**
	 * Return the feed type
	 *
	 * @return string
	 */
	public function getFeedType()
	{
		return $this->feedType;
	}
}
