<?php

namespace Swiftlet\Models;

class FeedItem extends \Swiftlet\Model
{
	protected
		$id,
		$feed,
		$xml
		;

	/**
	 * Initialise a feed item
	 *
	 * @param @object $feed
	 * @param @object $xml
	 * @return object
	 */
	public function init(Feed $feed, \SimpleXMLElement $xml)
	{
		$this->feed = $feed;
		$this->xml  = $xml;

		return $this;
	}

	public function save()
	{
		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			INSERT IGNORE INTO items (
				url,
				title,
				contents,
				posted_at,
				feed_id,
				created_at
			) VALUES (
				:url,
				:title,
				:contents,
				:posted_at,
				:feed_id,
				UTC_TIMESTAMP()
			)
			;');

		$data = $this->getData();

		$sth->bindParam('title',     $data->title);
		$sth->bindParam('url',       $data->url);
		$sth->bindParam('contents',  $data->contents);
		$sth->bindParam('posted_at', $data->postedAt);
		$sth->bindParam('feed_id',   $this->feed->id);

		$sth->execute();

		$this->id = $dbh->lastInsertId();

		if ( $this->id ) {
			// Extract words
			$contents = trim(preg_replace('/\s+/', ' ', preg_replace('/\b([0-9]+.)\b/', ' ', preg_replace('/\W/', ' ', preg_replace('/&[a-z]+/', '', strtolower(strip_tags($data->title . ' ' . $data->contents)))))));

			$words = explode(' ', $contents);

			$wordsCount = array();

			foreach ( $words as $word ) {
				if ( !isset($wordsCount[$word]) ) {
					$wordsCount[$word] = 0;
				}

				$wordsCount[$word] ++;
			}

			$sth = $dbh->prepare('
				INSERT IGNORE INTO words (
					word
				) VALUES ' . implode(', ', array_fill(0, count($words), '( ? )')) . '
				;');

			$i = 1;

			foreach( $words as $key => $word ) {
				$sth->bindParam($i ++, $words[$key]);
			}

			$sth->execute();

			// Link item to words
			$inserts = array();

			foreach( $words as $word ) {
				$inserts[] = array(
					'id'    => $this->id,
					'word'  => $word,
					'count' => $wordsCount[$word]
					);
			}

			$sth = $dbh->prepare('
				INSERT IGNORE INTO items_words (
					item_id,
					word_id,
					count
				) VALUES ' . implode(', ', array_fill(0, count($words), '( ?, ( SELECT id FROM words WHERE word = ? LIMIT 1 ), ? )')) . '
				;');

			$i = 1;

			foreach( $words as $key => $word ) {
				$sth->bindParam($i ++, $this->id);
				$sth->bindParam($i ++, $words[$key]);
				$sth->bindParam($i ++, $wordsCount[$word]);
			}

			$sth->execute();
		}
	}

	/**
	 * Get item data
	 *
	 * @return object
	 */
	private function getData()
	{
		$data = new \stdClass;

		switch ( $this->feed->getType() ) {
			case 'rss':
				$data->url      = (string) $this->xml->link;
				$data->title    = (string) $this->xml->title;
				$data->contents = (string) $this->xml->description;
				$data->postedAt = date('Y-m-d H:i', strtotime((string) $this->xml->pubDate));

				break;
			case 'atom':
				$data->url      = (string) $this->xml->link->attributes()->href;
				$data->title    = (string) $this->xml->title;
				$data->contents = (string) $this->xml->content;
				$data->postedAt = date('Y-m-d H:i', strtotime((string) $this->xml->published));

				break;
			case 'rss-rdf':
				$content = $this->xml->children('http://purl.org/rss/1.0/modules/content/');
				$dc      = $this->xml->children('http://purl.org/dc/elements/1.1/');

				$data->url      = (string) $this->xml->link;
				$data->title    = (string) $this->xml->title;
				$data->contents = (string) $content->encoded;
				$data->postedAt = date('Y-m-d H:i', strtotime((string) $dc->date));

				break;
		}

		return $data;
	}
}
