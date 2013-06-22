<?php require 'header.php' ?>

<h1><?= $this->get('pageTitle') ?></h1>

<div class="jump">
	<p>
		Jump to:
	</p>

	<ul>
		<li><a href="#shortcuts">Keyboard shortcuts</a></li>
		<li><a href="#contact">Contact</a></li>
	</ul>
</div>

<p>
	Readable.cc is a <a href="https://en.wikipedia.org/wiki/News_aggregator">news reader</a>.
</p>

<p>
	News is aggregated from <a href="https://en.wikipedia.org/wiki/Web_feed">web feeds</a>, a data format used by publishers to allows users to subscribe to updates.
	Most blogs and news websites offer such a feed.
</p>

<p>
	By signing up for a free account you can manage your own feeds. Supported formats include <a href="https://en.wikipedia.org/wiki/RSS">RSS</a> and <a href="https://en.wikipedia.org/wiki/Atom_%28standard%29">Atom</a>.
</p>

<p>
	New and interesting content is automatically promoted to the top of your reading list based on articles you vote for. Content the majority is likely to find
	interesting appears on the &lsquo;<a href="<?= $this->app->getRootPath() ?>">Popular Reading</a>&rsquo; page.
</p>

<p>
	Headlines are greyed out and stricken through when we believe you will find the article boring. If we guessed wrong, mark the item as &lsquo;interesting&rsquo;
	to help the system understand your interests better.
</p>

<p>
	Organise your subscriptions in folders. Folders are public and can be shared anonymously with anyone.
</p>

<p>
	Hit the &lsquo;save&rsquo; button on articles you wish to read later. They will appear on the &lsquo;<a href="<?= $this->app->getRootPath() ?>saved">Saved</a>&rsquo; page.
</p>

<p>
	<?= $this->app->getConfig('siteName') ?> is free and <a href="<?= $this->app->getConfig('repoUrl') ?>">open source</a>.
</p>

<div class="divider"></div>

<h2 id="shortcuts">Keyboard shortcuts</h2>

<p>
	Keyboard shortcuts to help you move around on the reading lists.
</p>

<table class="table">
	<thead>
		<tr>
			<th>Key</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><code>j</code>, <code>Spacebar</code></td>
			<td>Next article</td>
		</tr>
		<tr>
			<td><code>k</code></td>
			<td>Previous article</td>
		</tr>
		<tr>
			<td><code>o</code>, <code>Enter</code></td>
			<td>Expand article (open)</td>
		</tr>
		<tr>
			<td><code>c</code></td>
			<td>Collapse article (double-tap on mobile)</td>
		</tr>
		<tr>
			<td><code>s</code></td>
			<td>Toggle save article</td>
		</tr>
		<tr>
			<td><code>m</code>, <code>a</code></td>
			<td>Mark all articles read in &lsquo;<a href="<?= $this->app->getRootPath() ?>reading">My Reading</a>&rsquo;</td>
		</tr>
		<tr>
			<td><code>Escape</code></td>
			<td>Dismiss dialog box</td>
		</tr>
	</tbody>
</table>

<div class="divider"></div>

<h2 id="contact">Contact</h2>

<p>
	If you have any suggestions, questions or just want to say hi, please send an email to:
</p>

<p>
	&nbsp; <em><a class="contact-email" href="mailto:<?= $this->app->getConfig('emailHoneyPot') ?>"><?= $this->app->getConfig('emailHoneyPot') ?></a></em>
</p>

<p>
	Follow <?= $this->app->getConfig('siteName') ?> on Twitter:
</p>

<p>
	&nbsp; <em><a href="https://twitter.com/<?= $this->app->getConfig('twitterHandle') ?>">@<?= $this->app->getConfig('twitterHandle') ?></a></em>
</p>

<div class="divider"></div>

<h2>Attributions</h2>

<p>
	<small>Entypo pictograms by Daniel Bruce — <a href="http://www.entypo.com">www.entypo.com</a></small>
</p>

<p>
	<small><a href="http://readable.cc">Readable.cc</a> was created by <a href="http://alias.io">Elbert Alias</a></small>
</p>

<?php require 'footer.php' ?>
