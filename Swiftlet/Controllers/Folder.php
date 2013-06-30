<?php

namespace Swiftlet\Controllers;

class Folder extends \Swiftlet\Controllers\Read
{
	protected
		$title = 'Folder'
		;

	/**
	 * View feed
	 */
	public function view()
	{
		$this->getItems();
	}

	/**
	 * Get personal items
	 */
	public function items()
	{
		if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			$this->view->name = 'read';
		}

		$this->getItems();
	}

	/**
	 * Get personal items
	 */
	protected function getItems()
	{
		$userId = $this->app->getSingleton('session')->get('id');

		$excludes = !empty($_GET['excludes']) ? explode(' ', $_GET['excludes'])  : array();
		$page     = !empty($_GET['page'])     ? max(1, (int) abs($_GET['page'])) : 1;

		$args = $this->app->getArgs();

		$folderId = !empty($args[0]) ? $args[0] : null;

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
			SELECT
				title
			FROM folders
			WHERE
				id = :id
			LIMIT 1
			');

		$sth->bindParam('id', $folderId, \PDO::PARAM_INT);

		$sth->execute();

		$folder = $sth->fetch(\PDO::FETCH_OBJ);

		if ( !$folder ) {
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');

			$this->view->set('pageTitle', 'Error 404');

			$this->view->name = 'error404';
		} else {
			$this->view->set('pageTitle',       $folder->title);
			$this->view->set('pageDescription', '');
			$this->view->set('title',           $folder->title);

			// Redirect to full URL
			$folderLink = $this->app->getSingleton('helper')->getFolderLink($folderId, $folder->title);

			$args = $this->app->getArgs();

			if ( $folderLink != '/folder/view/' . $folderId . '/' && ( empty($args[1]) || '/folder/view/' . $folderId . '/' . $args[1] !== $folderLink ) ) {
				header('HTTP/1.1 301 Moved Permanently');
				header('Status: 301 Moved Permanently');
				header('Location: ' . $folderLink);

				exit;
			}

			if ( $userId ) {
				// Fetch items
				$sth = $dbh->prepare('
					SELECT
						feeds.id        AS feed_id,
						feeds.title     AS feed_title,
						feeds.link      AS feed_link,
						items.id,
						items.url,
						items.title,
						items.contents,
						items.posted_at,
						users_feeds.folder_id,
						COALESCE(users_items.vote,  0) AS vote,
						COALESCE(users_items.score, 0) AS score,
						COALESCE(users_items.saved, 0) AS starred,
						1 AS feed_subscribed
					FROM       folders
					INNER JOIN users_feeds ON users_feeds.folder_id =     folders.id
					INNER JOIN feeds       ON       feeds.id        = users_feeds.feed_id
					INNER JOIN items       ON       items.feed_id   =       feeds.id
					LEFT  JOIN users_items ON users_items.item_id   =       items.id      AND users_items.user_id = ?
					WHERE
						folders.id         = ? AND
						( users_items.read = 0 OR users_items.read IS NULL )
						' . ( $excludes ? 'AND items.id NOT IN ( ' . implode(', ', array_fill(0, count($excludes), '?')) . ' )' : '' ) . '
					ORDER BY DATE(IF(items.posted_at, items.posted_at, items.created_at)) DESC, users_items.score DESC
					LIMIT ?
					');

				$i = 1;

				$sth->bindParam($i ++, $userId,   \PDO::PARAM_INT);
				$sth->bindParam($i ++, $folderId, \PDO::PARAM_INT);

				foreach( $excludes as $key => $itemId ) {
					$sth->bindParam($i ++, $excludes[$key], \PDO::PARAM_INT);
				}

				$limit = self::ITEMS_PER_PAGE;

				$sth->bindParam($i ++, $limit, \PDO::PARAM_INT);
			} else {
				$sth = $dbh->prepare('
					SELECT
						feeds.id         AS feed_id,
						feeds.title      AS feed_title,
						feeds.link       AS feed_link,
						items.id,
						items.url,
						items.title,
						items.contents,
						items.posted_at,
						folders.id       AS folder_id,
						0                AS vote,
						0                AS starred,
						0                AS score,
						0                AS feed_subscribed
					FROM       folders
					INNER JOIN users_feeds ON users_feeds.folder_id =     folders.id
					INNER JOIN feeds       ON       feeds.id        = users_feeds.feed_id
					INNER JOIN items       ON       items.feed_id   =       feeds.id
					WHERE
						folders.id = :folder_id
					LIMIT :limit_from, :limit_count
					');

				$limitFrom  = ( $page - 1 ) * self::ITEMS_PER_PAGE;
				$limitCount = self::ITEMS_PER_PAGE;

				$sth->bindParam('folder_id',   $folderId,   \PDO::PARAM_INT);
				$sth->bindParam('limit_from',  $limitFrom,  \PDO::PARAM_INT);
				$sth->bindParam('limit_count', $limitCount, \PDO::PARAM_INT);
			}

			$sth->execute();

			$result = $sth->fetchAll(\PDO::FETCH_OBJ);

			$items = $result;

			$this->prepare($items);

			$this->view->set('items', $items);
		}
	}
}
