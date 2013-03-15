<?php

namespace Swiftlet\Controllers;

class Read extends \Swiftlet\Controller
{
	/**
	 * Default action
	 */
	public function index()
	{
		header('HTTP/1.0 403 Forbidden');

		exit;
	}

	/**
	 * Sanitise HTML
	 *
	 * @param string $html
	 * @return string
	 */
	protected function purify(&$html)
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
	}
}
