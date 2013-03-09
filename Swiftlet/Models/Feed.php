<?php

namespace Swiftlet\Models;

class Feed extends \Swiftlet\Model
{
	const
		FEED_INVALID = 1,
		SERVER_ERROR = 2
		;

	public
		$id,
		$timeout = 6
		;

	protected
		$effectiveUrl,
		$contents,
		$xml,
		$feedType
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

		$this->effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

		$this->contents = substr($response, $headerSize);

		try {
			$xml = new \SimpleXMLElement($this->contents);

			if ( $xml->getName() == 'rss' ) {
				$this->xml = $xml;

				$this->feedType = 'rss';
			}
		} catch ( \Exception $e ) {
			throw new \Exception($e->getMessage(), FEED_INVALID);
		}
	}

	/**
	 * Get feed items
	 *
	 * return mixed
	 */
	public function getItems()
	{
		$items = array();

		switch ( $this->feedType ) {
			case 'rss':
				$items = $this->xml->channel->item;

				break;
		}

		return $items;
	}

	/**
	 * Get the effective URL
	 *
	 * @return string
	 */
	public function getEffectiveUrl()
	{
		return $this->effectiveUrl;
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
