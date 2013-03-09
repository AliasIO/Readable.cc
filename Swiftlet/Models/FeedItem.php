<?php

namespace Swiftlet\Models;

class FeedItem extends \Swiftlet\Model
{
	public
		$id,
		$feed,
		$xml
		;

	public function save()
	{
		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			INSERT IGNORE INTO items (
				url,
				title,
				contents,
				posted_at,
				feed_id
			) VALUES (
				:url,
				:title,
				:contents,
				:posted_at,
				:feed_id
			)
			;');

		$data = $this->_getData();

		$sth->bindParam('title',     $data->title);
		$sth->bindParam('url',       $data->url);
		$sth->bindParam('contents',  $data->contents);
		$sth->bindParam('posted_at', $data->postedAt);
		$sth->bindParam('feed_id',   $this->feed->id);

		$sth->execute();

		$this->id = $dbh->lastInsertId();

		if ( $this->id ) {
			// Extract words
			$contents = trim(preg_replace('/\s+/', ' ', preg_replace('/\b([0-9]+.)\b/', ' ', preg_replace('/\W/', ' ', preg_replace('/&[a-z]+/', '', strtolower(strip_tags($data->contents)))))));

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

	private function _getData()
	{
		$data = (object) array(
			'title'    => '',
			'url'      => '',
			'contents' => '',
			'postedAt' => ''
			);

		switch ( $this->feed->getFeedType() ) {
			case 'rss':
				$data = (object) array(
					'title'    => $this->xml->title,
					'url'      => $this->xml->link,
					'contents' => $this->xml->description,
					'postedAt' => date('Y-m-d H:i:s', strtotime($this->xml->pubDate))
					);

				break;
		}

		return $data;
	}
}
