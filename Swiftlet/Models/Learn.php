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
				UPDATE LOW_PRIORITY users SET
					last_learned_at = UTC_TIMESTAMP()
				WHERE
					users.id IN ( ' . implode(', ', $userIds) . ' )
				LIMIT 1000
				;');

			$sth->execute();

			// Rank words
			foreach ( $userIds as $userId ) {
				$sth = $dbh->prepare('
					REPLACE LOW_PRIORITY INTO users_words (
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
							items_words.word_id,
							users_items.user_id,
							SUM(users_items.vote) AS vote
						FROM (
							SELECT
								users_items.item_id,
								users_items.user_id,
								users_items.vote
							FROM      items
							INNER JOIN users_items ON users_items.item_id = items.id AND users_items.vote != 0 AND users_items.user_id = :user_id
							ORDER BY items.id DESC
							LIMIT 1000
						) AS users_items
						INNER JOIN items_words ON items_words.item_id = users_items.item_id
						GROUP BY items_words.word_id
						ORDER BY SUM(items_words.`count`) DESC
					) AS main, (
						SELECT @row := 0
					) AS rownum
					;');

				$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

				$sth->execute();
			}
		}

		// Rank items
		if ( $itemIds ) {
			$sth = $dbh->prepare('
				INSERT LOW_PRIORITY INTO users_items (
					user_id,
					item_id,
					score
					)
				SELECT
					user_id,
					item_id,
					SUM(score) AS score
				FROM (
					SELECT
						users_feeds.user_id,
						items.id    AS item_id,
						users_words.score * CAST(items_words.count AS SIGNED) AS score
					FROM       users_feeds
					INNER JOIN       users ON users.id            = users_feeds.user_id
					INNER JOIN       items ON items.feed_id       = users_feeds.feed_id
					LEFT  JOIN users_items ON users_items.item_id =       items.id      AND users_items.vote != 0
					LEFT  JOIN items_words ON items_words.item_id =       items.id
					LEFT  JOIN users_words ON users_words.word_id = items_words.word_id AND users_words.user_id = users.id
					WHERE
						items.id             IN ( ' . implode(', ', $itemIds) . ' )       AND -- Learn only for new items
						items.short          = 0                                          AND -- Learn only for long items
						users.enabled        = 1                                          AND -- Learn only for enabled users
						users.last_active_at > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY)     -- Learn only for active users
					) AS main
				GROUP BY user_id, item_id
				ON DUPLICATE KEY UPDATE
					score = VALUES(score)
				;');

			$sth->execute();

			$sth = $dbh->prepare('
				UPDATE LOW_PRIORITY items
				INNER JOIN (
					SELECT
						users_items.item_id,
						AVG(users_items.score) AS score
					FROM users_items
					WHERE
						users_items.item_id IN ( ' . implode(', ', $itemIds) . ' )
					GROUP BY users_items.item_id
					) AS main ON main.item_id = items.id
				SET
					items.score = main.score
				WHERE
					items.short = 0
				');

			$sth->execute();
		}

		return array(count($itemIds), count($userIds));
	}
}
