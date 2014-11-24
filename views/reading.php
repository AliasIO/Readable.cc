<?php require 'header.php' ?>

<div id="page-head-wrap">
	<div id="page-head">
		<h1><?= $this->get('pageTitle') ?></h1>

		<p>
			<span><a href="<?= $this->app->getRootPath() ?>subscriptions">Subscribe</a> to feeds for personalised reading.</span>
			<span>Click the star icon on articles you wish to <a href="<?= $this->app->getRootPath() ?>starred">read later</a>.</span>
			<span>Press &lsquo;m&rsquo; to <a class="mark-all-read" href="javascript: void(0);">mark all items in your reading list read</a>.</span>
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
	<?php require 'read.php' ?>
</div>
<?php endif ?>

<?php require 'footer.php' ?>
