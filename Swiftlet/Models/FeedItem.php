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
			INSERT LOW_PRIORITY IGNORE INTO items (
				url,
				title,
				contents,
				language,
				english,
				posted_at,
				feed_id,
				short,
				created_at
			) VALUES (
				:url,
				:title,
				:contents,
				:language,
				:english,
				:posted_at,
				:feed_id,
				:short,
				UTC_TIMESTAMP()
			)
			');

		$data = $this->getData();

		if ( !$data ) {
			return;
		}

		$sth->bindParam('title',     $data->title);
		$sth->bindParam('url',       $data->url);
		$sth->bindParam('contents',  $data->contents);
		$sth->bindParam('language',  $data->language);
		$sth->bindParam('english',   $data->english);
		$sth->bindParam('posted_at', $data->postedAt);
		$sth->bindParam('feed_id',   $this->feed->id, \PDO::PARAM_INT);
		$sth->bindParam('short',     $data->short,    \PDO::PARAM_INT);

		$sth->execute();

		$this->id = $dbh->lastInsertId();

		if ( $this->id ) {
			// Extract words
			$words = $this->app->getSingleton('helper')->extractWords($data->title . ' ' . $data->contents);

			if ( $words ) {
				$wordsCount = array();

				foreach ( $words as $word ) {
					if ( !isset($wordsCount[$word]) ) {
						$wordsCount[$word] = 0;
					}

					$wordsCount[$word] ++;
				}

				$sth = $dbh->prepare('
					INSERT LOW_PRIORITY IGNORE INTO words (
						word
					) VALUES ' . implode(', ', array_fill(0, count($words), '( ? )')) . '
					');

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
					INSERT LOW_PRIORITY IGNORE INTO items_words (
						item_id,
						word_id,
						count
					) VALUES ' . implode(', ', array_fill(0, count($words), '( ?, ( SELECT id FROM words WHERE word = ? LIMIT 1 ), ? )')) . '
					');

				$i = 1;

				foreach( $words as $key => $word ) {
					$sth->bindParam($i ++, $this->id,          \PDO::PARAM_INT);
					$sth->bindParam($i ++, $words[$key]);
					$sth->bindParam($i ++, $wordsCount[$word], \PDO::PARAM_INT);
				}

				$sth->execute();
			}
		}
	}

	/**
	 * Get item ID
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
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
				$contents = $nsContent->encoded ? $nsContent->encoded : ( $this->xml->description ? $this->xml->description : false );

				$data->url      = (string) $this->xml->link;
				$data->title    = (string) $this->xml->title;
				$data->contents = $contents ? (string) $contents : '';
				$data->postedAt = $this->xml->pubDate ? date('Y-m-d H:i', strtotime((string) $this->xml->pubDate)) : null;
				$data->language = $this->feed->getLanguage();

				break;
			case 'atom':
				foreach ( $this->xml->link as $link ) {
					if ( $link->attributes()->rel == 'alternate' ) {
						break;
					}
				}

				if ( !isset($link) ) {
					return new \stdClass;
				}

				$contents = $this->xml->content ? $this->xml->content : ( $this->xml->summary ? $this->xml->summary : false );

				$date = $this->xml->published ? $this->xml->published : ( $this->xml->updated ? $this->xml->updated : false );

				$language = $this->xml->content ? (string) $this->xml->content->attributes(Feed::NS_XML)->lang : '';

				if ( !$language ) {
					$language = $this->feed->getLanguage();
				}

				$data->url      = (string) $link->attributes()->href;
				$data->title    = (string) $this->xml->title;
				$data->contents = $contents ? (string) $contents : '';
				$data->postedAt = $date ? date('Y-m-d H:i:s', strtotime((string) $date)) : null;
				$data->language = $language;

				break;
			case 'rss1':
			case 'rss1-rdf':
				$contents = $nsContent->encoded ? $nsContent->encoded : ( $this->xml->description ? $this->xml->description : false );

				$date = $this->xml->pubDate ? $this->xml->pubDate : ( $nsDc->date ? $nsDc->date : false );

				$data->url      = (string) $this->xml->link;
				$data->title    = (string) $this->xml->title;
				$data->contents = $contents ? (string) $contents : '';
				$data->postedAt = $date ? date('Y-m-d H:i:s', strtotime((string) $date)) : null;
				$data->language = $this->feed->getLanguage();

				break;
		}

		$data->short = strlen(strip_tags($data->contents)) < 1000 ? 1 : 0;

		$data->english = substr(strtolower($data->language), 0, 2) == 'en';

		return $data;
	}
}
