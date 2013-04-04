<?php

namespace Swiftlet\Models;

class Learn extends \Swiftlet\Model
{
	/**
	 * TODO
	 *
	 * @param int $itemIds
	 */
	public function learn($itemIds)
	{
		$dbh = $this->app->getSingleton('pdo')->getHandle();

		// Update users
		$userIds = array();

		$sth = $dbh->prepare('
			SELECT
				users.id
			FROM users
			WHERE
			  	enabled         = 1                                                                      AND -- Learn only for enabled users
				( last_learned_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY) OR last_learned_at IS NULL ) AND -- Learn once a day at most
			    last_active_at  > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY)                                  -- Learn only for recently active users
			LIMIT 1000
			;');

		$sth->execute();

		$users = $sth->fetchAll(\PDO::FETCH_OBJ);

		foreach ( $users as $user ) {
			$userIds[] = $user->id;
		}

		if ( $userIds ) {
			$sth = $dbh->prepare('
				UPDATE users SET
					last_learned_at = UTC_TIMESTAMP()
				WHERE
					users.id IN ( ' . implode(', ', $userIds) . ' )
				LIMIT 1000
				;');

			$sth->execute();

			// Rank words
			foreach ( $userIds as $userId ) {
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
			}
		}

		// Rank items
		if ( $itemIds ) {
			$sth = $dbh->prepare('
				INSERT INTO users_items (
					user_id,
					item_id,
					score
					)
				SELECT
					users_feeds.user_id,
					items.id,
					COALESCE(SUM(users_words.score), 0)
				FROM       users_feeds
				INNER JOIN       users ON users.id            = users_feeds.user_id
				INNER JOIN       items ON items.feed_id       = users_feeds.feed_id
				LEFT  JOIN users_items ON users_items.item_id =       items.id
				LEFT  JOIN items_words ON items_words.item_id =       items.id
				LEFT  JOIN users_words ON users_words.word_id = items_words.word_id
				WHERE
						items.id              IN ( ' . implode(', ', $itemIds) . ' )      AND -- Learn only for new items
						users.enabled         = 1                                         AND -- Learn only for enabled users
						users.last_active_at  > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY)     -- Learn only for recently active users
				GROUP BY users_feeds.user_id, items.id
				LIMIT 1000000
				ON DUPLICATE KEY UPDATE
					score = VALUES(score)
				;');

			$sth->bindParam('user_id', $userId);

			$sth->execute();
		}

		return array(count($itemIds), count($userIds));
	}
}
