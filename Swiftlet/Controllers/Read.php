<?php

namespace Swiftlet\Controllers;

class Read extends \Swiftlet\Controller
{
	const
		ITEMS_PER_PAGE = 25
		;

	/**
	 * Constructor
	 * @param object $app
	 * @param object $view
	 */
	public function __construct(\Swiftlet\Interfaces\App $app, \Swiftlet\Interfaces\View $view)
	{
		parent::__construct($app, $view);
	}

	/**
	 * Default action
	 */
	public function index()
	{
		header('HTTP/1.0 403 Forbidden');

		exit;
	}

	/**
	 * Register vote
	 *
	 * @throws \Swiftlet\Exception
	 */
	public function vote()
	{
		header('Content-type: application/json');

		$userId = $this->app->getSingleton('helper')->ensureValidUser(true);

		$itemId = isset($_POST['item_id']) ? (int) $_POST['item_id'] : null;
		$vote   = isset($_POST['vote'])    ? (int) $_POST['vote']    : null;

		if ( !$itemId || $vote < -1 || $vote > 1 ) {
			header('HTTP/1.0 400 Bad Request');

			exit(json_encode(array('message' => 'Invalid arguments')));
		}

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
      INSERT IGNORE INTO users_items (
        user_id,
        item_id,
        vote
      ) VALUES (
				:user_id,
				:item_id,
				:vote
      )
      ON DUPLICATE KEY UPDATE
        vote = :vote
			');

		$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);
		$sth->bindParam('item_id', $itemId, \PDO::PARAM_INT);
		$sth->bindParam('vote',    $vote,   \PDO::PARAM_INT);

		try {
			$sth->execute();
		} catch ( \Swiftlet\Exception $e ) {
			header('HTTP/1.0 500 Server Error');

			exit(json_encode(array('message' => 'Something went wrong, please try again.')));
		}

		exit(json_encode(array()));
	}

	/**
	 * Mark item as read
	 */
	public function markRead()
	{
		header('Content-type: application/json');

		$userId = $this->app->getSingleton('helper')->ensureValidUser(true);

		$itemId   = isset($_POST['item_id'])   ? (int) $_POST['item_id']   : null;
		$folderId = isset($_POST['folder_id']) ? (int) $_POST['folder_id'] : null;

		if ( $_POST['item_id'] == 'all' ) {
			$dbh = $this->app->getSingleton('pdo')->getHandle();

			$sth = $dbh->prepare('
				INSERT IGNORE INTO users_items (
					user_id,
					item_id,
					`read`
					)
				SELECT
					:user_id,
					items.id,
					1
				FROM       users_feeds
				' . ( $folderId ? '
				INNER JOIN folders     ON folders.id      = users_feeds.folder_id AND folders.id = :folder_id
		 		' : '' ) . '
				INNER JOIN items       ON   items.feed_id = users_feeds.feed_id
				WHERE
					users_feeds.user_id = :user_id
				ON DUPLICATE KEY UPDATE
					`read` = 1
				');

			$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);

			if ( $folderId ) {
				$sth->bindParam('folder_id', $folderId, \PDO::PARAM_INT);
			}

			$sth->execute();
		} else {
			if ( !$itemId ) {
				header('HTTP/1.0 400 Bad Request');

				exit(json_encode(array('message' => 'Invalid arguments')));
			}

			$dbh = $this->app->getSingleton('pdo')->getHandle();

			$sth = $dbh->prepare('
				INSERT IGNORE INTO users_items (
					user_id,
					item_id,
					`read`
				) VALUES (
					:user_id,
					:item_id,
					1
				)
				ON DUPLICATE KEY UPDATE
					`read` = 1
				');

			$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);
			$sth->bindParam('item_id', $itemId, \PDO::PARAM_INT);

			$sth->execute();
		}

		exit(json_encode(array()));
	}

	/**
	 * Star item
	 */
	public function star()
	{
		header('Content-type: application/json');

		$userId = $this->app->getSingleton('helper')->ensureValidUser(true);

		$itemId = isset($_POST['item_id']) ? (int) $_POST['item_id'] : null;
		$star   = isset($_POST['star'])    ? (int) $_POST['star']    : null;

		if ( !$itemId || ( $star != 0 && $star != 1 ) ) {
			header('HTTP/1.0 400 Bad Request');

			exit(json_encode(array('message' => 'Invalid arguments')));
		}

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
      INSERT IGNORE INTO users_items (
        user_id,
        item_id,
        saved
      ) VALUES (
				:user_id,
				:item_id,
				:saved
      )
      ON DUPLICATE KEY UPDATE
        saved = :saved
			');

		$sth->bindParam('user_id', $userId, \PDO::PARAM_INT);
		$sth->bindParam('item_id', $itemId, \PDO::PARAM_INT);
		$sth->bindParam('saved',   $star,   \PDO::PARAM_INT);

		$sth->execute();

		exit(json_encode(array()));
	}

	/**
	 * Subscribe to feed
	 */
	public function subscribe()
	{
		header('Content-type: application/json');

		$userId = $this->app->getSingleton('helper')->ensureValidUser(true);

		$feedId = isset($_POST['feed_id']) ? (int) $_POST['feed_id'] : null;
		$action = isset($_POST['action'])  ?       $_POST['action']  : null;

		if ( !$feedId || ( $action != 'subscribe' && $action != 'unsubscribe' ) ) {
			header('HTTP/1.0 400 Bad Request');

			exit(json_encode(array('message' => 'Invalid arguments')));
		}

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		if ( $action == 'subscribe' ) {
			$this->app->getSingleton('subscription')->subscribe($feedId);
		} else {
			$this->app->getSingleton('subscription')->unsubscribe($feedId);
		}

		exit(json_encode(array()));
	}

	/**
	 * Prepare feed items for display
	 *
	 * @param array $items
	 */
	protected function prepare(&$items)
	{
		foreach ( $items as $item ) {
			$this->purify($item->contents, 'http://' . parse_url($item->feed_link, PHP_URL_HOST) . '/');

			$this->app->getSingleton('helper')->localize($item->posted_at);

			$item->title = $this->view->htmlDecode(strip_tags($item->title));
		}
	}

	/**
	 * Sanitise HTML
	 *
	 * @param string $html
	 * @return string
	 */
	protected function purify(&$html, $baseUrl = '')
	{
		require_once 'HTMLPurifier/Bootstrap.php';

		// Remove FeedBurner cruft
		$html = preg_replace('/(<div class="feedflare.+?<\/div>|<img[^>]+?(feedsportal|feedburner)\.com[^>]+?>)/is', '', $html);

		// Captions
		$html = preg_replace('/<(figcaption|div class="caption(-(byline|text))?")[^>]*>/i', '<p class="caption">', $html);

		// Covert various block level sections to paragraphs
		$html = preg_replace('/<(\/)?(center|div|figure|figcaption|section)[^>]*>/i', '<$1p>', $html);

		// Prevent autoParagraph from removing block level elements
		$html = preg_replace('/(<\/?(h1|h2|h3|h4|h5|h6|ul|ol|blockquote|pre)[^>]*>)/i', "\n$1\n", $html);

		// Multiple linebreaks to newline
		$html = preg_replace('/(\s*<br ?\/?>\s*){2,}/s', "\n\n", $html);

		$config = \HTMLPurifier_Config::createDefault();

		$config->set('HTML.Allowed', 'h1,h2,h3,h4,h5,h6,a[href],p[class],ul,ol,li,blockquote,em,i,strong,b,img[src],pre,code,table,thead,tbody,tfoot,tr,th,td,iframe[src|frameborder],br,small');
		$config->set('AutoFormat.AutoParagraph', true);
		$config->set('AutoFormat.RemoveEmpty', true);
		$config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
		$config->set('Attr.AllowedClasses', array('caption'));
		$config->set('HTML.SafeIframe', true);
		$config->set('URI.SafeIframeRegexp', '%^http://(www.youtube(?:-nocookie)?.com/embed/|player.vimeo.com/video/)%'); //allow YouTube and Vimeo

		$purifier = new \HTMLPurifier($config);

		$html = $purifier->purify($html);

		// First and last child line breaks to newlines
		$html = preg_replace('/(<[^\/]+>)\s*<br ?\/?>/is', "$1\n\n", $html);
		$html = preg_replace('/<br ?\/?>\s*</is', "\n\n<", $html);

		// Ensure lost inline elements are wrapped in paragraphs
		$html = preg_replace('/(em|strong|i|b|small)>\s*</is', "$1>\n\n<", $html);

		// AutoParagraph a second time after elements have been cleaned up
		$html = $purifier->purify($html);

		// Remove empty paragraph elements
		$html = preg_replace('/\s*<p><\/p>\is*/', "", $html);

		preg_match_all('/(href|src)=("|\')([^"\']+)\2/', $html, $matches);

		foreach ( $matches[3] as $url ) {
			if ( !preg_match('/^http/', $url) ) {
				$html = str_replace($url, $baseUrl . ltrim($url, '/'), $html);
			}
		}

		// Remove src attribute from images for lazy loading
		$html = preg_replace('/(<img[^>]+src=(["\']))([^"\']+)(\2[^>]+>)/', '\1' . $this->app->getRootPath() . 'images/loading-image.gif\2 data-src="\3\4', $html);
	}
}
