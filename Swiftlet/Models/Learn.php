<?php

namespace Swiftlet\Models;

class Learn extends \Swiftlet\Model
{
	/**
	 * TODO
	 *
	 * @param int $userId
	 */
	public function learn($userId)
	{
		$dbh = $this->app->getSingleton('pdo')->getHandle();

		// Update user
		$sth = $dbh->prepare('
			UPDATE users SET
				last_learned_at = UTC_TIMESTAMP()
			WHERE
				id = :id
			;');

		$sth->bindParam('id', $userId);

		$sth->execute();

		// Rank words
		$sth = $dbh->prepare('
      REPLACE INTO users_words (
        user_id,
        word_id,
        score
      )
      SELECT
        main.user_id                     AS user_id,
        main.word_id                     AS word_id,
        main.vote * ( @row := @row + 1 ) AS score
      FROM (
        SELECT
					words.id              AS word_id,
					users_items.user_id   AS user_id,
					SUM(users_items.vote) AS vote
        FROM      words
        LEFT JOIN items_words ON       words.id      = items_words.word_id
        LEFT JOIN items       ON items_words.item_id =       items.id
        LEFT JOIN users_items ON       items.id      = users_items.item_id
        WHERE
          users_items.user_id = :user_id
        GROUP BY words.id
        ORDER BY count DESC
      ) AS main, (
        SELECT @row := 0
      ) AS rownum
			;');

		$sth->bindParam('user_id', $userId);

		$sth->execute();

		// Rank items
		$sth = $dbh->prepare('
			INSERT INTO users_items (
				user_id,
				item_id,
				score
				)
			SELECT
				:user_id,
				items.id,
				COALESCE(SUM(users_words.score), 0)
			FROM       users_feeds
			INNER JOIN       items ON items.feed_id       = users_feeds.feed_id
			LEFT  JOIN users_items ON users_items.item_id =       items.id
			LEFT  JOIN items_words ON items_words.item_id =       items.id
			LEFT  JOIN users_words ON users_words.word_id = items_words.word_id AND users_words.user_id = :user_id
			GROUP BY items.id
			ORDER BY created_at DESC
			LIMIT 1000
			ON DUPLICATE KEY UPDATE
				score = VALUES(score)
			;');

		$sth->bindParam('user_id', $userId);

		$sth->execute();
	}
}
