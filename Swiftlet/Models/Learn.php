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
			');

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
							STRAIGHT_JOIN users_items ON users_items.item_id = items.id AND users_items.vote != 0 AND users_items.user_id = :user_id
							ORDER BY items.id DESC
							LIMIT 1000
						) AS users_items
						STRAIGHT_JOIN items_words ON items_words.item_id = users_items.item_id
						GROUP BY items_words.word_id
						ORDER BY SUM(items_words.`count`) DESC
					) AS main, (
						SELECT @row := 0
					) AS rownum
					');

				$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

				$sth->execute();
			}
		}

		// Rank items
		if ( $itemIds ) {
			$sth = $dbh->prepare('
				SELECT
					user_id,
					item_id,
					SUM(score) AS score
				FROM (
					SELECT
						users_feeds.user_id,
						items.id AS item_id,
						users_words.score * CAST(items_words.count AS SIGNED) AS score
					FROM          items
					STRAIGHT_JOIN users_feeds ON users_feeds.feed_id =       items.feed_id
					STRAIGHT_JOIN users       ON users.id            = users_feeds.user_id AND users.enabled     = 1 AND users.last_active_at > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY)
					LEFT JOIN     users_items ON users_items.item_id =       items.id      AND users_items.vote != 0
					LEFT JOIN     items_words ON items_words.item_id =       items.id
					LEFT JOIN     users_words ON users_words.word_id = items_words.word_id AND users_words.user_id = users.id
					WHERE
						items.id IN ( ' . implode(', ', $itemIds) . ' ) AND
						items.short = 0
					) AS main
				GROUP BY user_id, item_id
				');

			$sth->execute();

			$results = $sth->fetchAll(\PDO::FETCH_OBJ);

			if ( $results ) {
				$sth = $dbh->prepare('
					INSERT LOW_PRIORITY INTO users_items (
						user_id,
						item_id,
						score
					) VALUES ' . implode(', ', array_fill(0, count($results), '( ?, ?, ? )')) . '
					ON DUPLICATE KEY UPDATE
						score = VALUES(score)
					');

				$i = 1;

				foreach ( $results as $key => $result ) {
					$sth->bindParam($i ++, $results[$key]->user_id, \PDO::PARAM_INT);
					$sth->bindParam($i ++, $results[$key]->item_id, \PDO::PARAM_INT);
					$sth->bindParam($i ++, $results[$key]->score,   \PDO::PARAM_INT);
				}

				$sth->execute();

				unset($results);
			}

			$sth = $dbh->prepare('
				SELECT
					items.id,
					AVG(users_items.score) AS score
				FROM                items
				STRAIGHT_JOIN users_items ON users_items.item_id = items.id
				WHERE
					items.id IN ( ' . implode(', ', $itemIds) . ' ) AND
					items.short = 0
				GROUP BY items.id
				');

			$sth->execute();

			$results = $sth->fetchAll(\PDO::FETCH_OBJ);

			if ( $results ) {
				foreach ( $results as $result ) {
					$sth = $dbh->prepare('
						UPDATE LOW_PRIORITY items
						SET
							score = :score
						WHERE
							id = :id
						');

					$sth->bindParam('id',    $result->id,    \PDO::PARAM_INT);
					$sth->bindParam('score', $result->score, \PDO::PARAM_INT);

					$sth->execute();
				}

				unset($results);
			}
		}

		return array(count($itemIds), count($userIds));
	}
}
