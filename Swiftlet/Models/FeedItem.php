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
				language,
				posted_at,
				feed_id,
				created_at
			) VALUES (
				:url,
				:title,
				:contents,
				:language,
				:posted_at,
				:feed_id,
				UTC_TIMESTAMP()
			)
			;');

		$data = $this->getData();

		$sth->bindParam('title',     $data->title);
		$sth->bindParam('url',       $data->url);
		$sth->bindParam('contents',  $data->contents);
		$sth->bindParam('language',  $data->language);
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

		$nsContent = $this->xml->children(Feed::NS_CONTENT);
		$nsDc      = $this->xml->children(Feed::NS_DC);

		switch ( $this->feed->getType() ) {
			case 'rss2':
				$contents = $nsContent->encoded ? $nsContent->encoded : $this->xml->description;

				$data->url      = (string) $this->xml->link;
				$data->title    = (string) $this->xml->title;
				$data->contents = (string) $contents;
				$data->postedAt = date('Y-m-d H:i', $this->xml->pubDate ? strtotime((string) $this->xml->pubDate) : time());
				$data->language = $this->feed->getLanguage();

				break;
			case 'atom':
				foreach ( $this->xml->link as $link ) {
					if ( $link->attributes()->rel == 'alternate' ) {
						break;
					}
				}

				$language = (string) $this->xml->content->attributes(Feed::NS_XML)->lang;

				if ( !$language ) {
					$language = $this->feed->getLanguage();
				}

				$data->url      = (string) $link->attributes()->href;
				$data->title    = (string) $this->xml->title;
				$data->contents = (string) $this->xml->content;
				$data->postedAt = date('Y-m-d H:i', $this->xml->published ? strtotime((string) $this->xml->published) : time());
				$data->language = $language;

				break;
			case 'rss1':
			case 'rss-rdf':
				$date = $this->xml->pubDate ? $this->xml->pubDate : ( $nsDc->date ? $nsDc->date : false );

				$data->url      = (string) $this->xml->link;
				$data->title    = (string) $this->xml->title;
				$data->contents = (string) $nsContent->encoded;
				$data->postedAt = date('Y-m-d H:i', $date ? strtotime((string) $date) : time());
				$data->language = $this->feed->getLanguage();

				break;
		}

		return $data;
	}
}
