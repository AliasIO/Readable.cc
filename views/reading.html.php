<?php require 'header.html.php' ?>

<div id="page-head-wrap">
	<div id="page-head">
		<h1>My Reading</h1>

		<p>
			<span><a href="/subscriptions">Subscribe</a> to feeds and vote on articles for personalised reading.</span>
			<span>Click &lsquo;save&rsquo; on articles you wish to <a href="/saved">read later</a>.</span>
			<span>Vote on articles for personalised reading.</span>
			<span>Press &lsquo;m&rsquo; to mark all items in your reading list read.</span>
		</p>
	</div>
</div>

<?php if ( $this->app->getAction() == 'index' && !$this->get('items', false) ): ?>
<div id="items-footer">
	<p>
		<i class="entypo chevron-small-left"></i> No unread articles, please come back later <i class="entypo chevron-small-right"></i>
	</p>
</div>
<?php else: ?>
<div id="items-read-line"></div>

<div id="items">
	<?php require 'read.html.php' ?>
</div>
<?php endif ?>

<?php require 'footer.html.php' ?>
