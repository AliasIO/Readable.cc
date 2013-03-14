<?php

namespace Swiftlet\Controllers;

class Index extends \Swiftlet\Controller
{
	protected
		$title = 'Popular'
		;

	/**
	 * Default action
	 */
	public function index()
	{
		$userId = $this->app->getSingleton('helper')->ensureValidUser();

		$this->app->getSingleton('learn')->learn($userId);
	}

	/**
	 * Get personal items
	 */
	public function items()
	{
		$this->view->name = 'feed-items';

		$dbh = $this->app->getSingleton('pdo')->getHandle();

		$sth = $dbh->prepare('
      SELECT
				feeds.title AS feed_title,
				feeds.link  AS feed_link,
				items.id,
				items.url,
				items.title,
				items.contents,
				items.posted_at,
				users_items.vote,
				users_items.score
			FROM       users_items
			INNER JOIN       items ON items.id = users_items.item_id
      INNER JOIN       feeds ON feeds.id =       items.feed_id
      ORDER BY score DESC, items.posted_at ASC
			LIMIT 500
			;');

		$sth->execute();

		$result = $sth->fetchAll(\PDO::FETCH_OBJ);

		$items = $result;

		foreach ( $items as $item ) {
			$item->contents = $this->_clean($item->contents);
		}

		$this->view->set('items', $items);
	}

	/**
	 * Sanitise HTML
	 *
	 * @param string $html
	 * @return string
	 */
	private function _clean($html)
	{
		require_once 'HTMLPurifier/Bootstrap.php';

		// Remove FeedBurner cruft
		$html = preg_replace('/(<div class="feedflare.+?<\/div>|<img[^>]+?(feedsportal|feedburner)\.com[^>]+?>)/s', '', $html);

		$config = \HTMLPurifier_Config::createDefault();

		$config->set('HTML.Allowed', 'h1,h2,h3,h4,h5,h6,a[href],p,ul,ol,li,blockquote,em,i,strong,b,img[src],pre,code,table,thead,tbody,tfoot,tr,th,td');
		$config->set('AutoFormat.AutoParagraph', true);
		$config->set('AutoFormat.RemoveEmpty', true);
		$config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
		$config->set('HTML.SafeObject', true);
		$config->set('Output.FlashCompat', true);

		$purifier = new \HTMLPurifier($config);

		$html = $purifier->purify($html);

		$html = preg_replace('/<table>/', '<table class="table table-bordered table-striped table-hover">', $html);

		return $html;
	}
}
