<?php if ( $this->app->getControllerName() === 'Reading' || $this->app->getControllerName() === 'Folder' ): ?>
<script>
	readable.itemCount = <?= $this->get('itemCount') ?>;
</script>
<?php endif ?>

<?php $items = $this->get('items', false) ?>

<?php if ( !$items ): ?>
<script>
	if ( readable.items ) {
		readable.items.noMoreItems = true;
	} else {
		readable.noMoreItems = true;
	}
</script>

<div id="items-footer">
	<p>
		<i class="entypo chevron-small-left"></i> No more articles <i class="entypo chevron-small-right"></i>
	</p>
</div>

<?php else: ?>

<?php foreach ( $items as $item ): ?>
<article
	data-item-id="<?= $item->id ?>"
	data-item-score="<?= $item->score ?>"
	data-item-url="<?= $this->htmlEncode($item->url) ?>"
	data-item-date="<?= date('F j, Y', $item->posted_at) ?>"
	data-feed-host="<?= parse_url($item->feed_link, PHP_URL_HOST) ?>"
	class="inactive collapsed <?= $item->score < 0 ? ' boring' : '' ?>"
	>
	<h1<?= $item->score < 0 ? ' title="You may find this article uninteresting (based on articles you voted on)"' : '' ?>>
		<a href="<?= $this->htmlEncode($item->url) ?>"><?= $item->title ?></a>
	</h1>

	<p class="item-date">
		By
		<strong><a href="<?= $this->app->getSingleton('helper')->getFeedLink($item->feed_id, $item->feed_title) ?>" title="<?= parse_url($item->feed_link, PHP_URL_HOST) ?>"><?= $item->feed_title ?></a></strong>
		<?= $item->posted_at ? 'on ' . date('F j, Y', $item->posted_at) : '' ?>
		<?php if ( $this->app->getControllerName() === 'Reading' && $item->folder_title ): ?>
		in <strong><a href="<?= $this->app->getSingleton('helper')->getFolderLink($item->folder_id, $item->folder_title) ?>"><?= $item->folder_title ?></a></strong>
		<?php endif ?>
		<span class="feed-options">
			&mdash;
			<?php if ( $this->get('controller') == 'index' ): ?>
			<a href="<?= $this->app->getRootPath() ?>report/article/<?= $item->id ?>" class="report" data-feed-id="<?= $item->feed_id ?>" title="Report inappropriate content">
				<i class="entypo flag"></i>Report
			</a>
			&nbsp;
			<?php endif ?>
			<a href="javascript: void(0);" class="subscription <?= $item->feed_subscribed ? 'unscubscribe' : 'subscribe' ?>" data-feed-id="<?= $item->feed_id ?>" title="Subscriptions appear in &lsquo;My Reading&rsquo;">
				<?php if ( $item->feed_subscribed ): ?>
				<i class="entypo squared-minus"></i>&nbsp;Unsubscribe
				<?php else: ?>
				<i class="entypo squared-plus"></i>&nbsp;Subscribe
				<?php endif ?>
			</a>
		</span>
	</p>

	<div class="item-wrap">
		<div class="item-contents">
			<?= $item->contents ?>
		</div>

		<div class="article-actions">
			<i data-item-id="<?= $item->id ?>"                class="item-star entypo star<?=        $item->starred    ? ' active' : '' ?>" title="Star this article to read later"></i>
			<i data-item-id="<?= $item->id ?>" data-vote="1"  class="item-vote entypo thumbs-up<?=   $item->vote ==  1 ? ' active' : '' ?>" title="Promote articles like these in &lsquo;My Reading&rsquo;"></i>
			<i data-item-id="<?= $item->id ?>" data-vote="-1" class="item-vote entypo thumbs-down<?= $item->vote == -1 ? ' active' : '' ?>" title="Demote articles like these in &lsquo;My Reading&rsquo;"></i>
			<div class="share">
				<i data-item-id="<?= $item->id ?>" class="item-share entypo share" title="Share this article"></i>

				<ul>
					<li><a href="http://www.facebook.com/sharer.php?s=100&amp;p[url]=<?= rawurlencode($item->url) ?>&amp;p[title]=<?= rawurlencode($item->title) ?>">Facebook</a></li>
					<li><a href="https://plus.google.com/share?url=<?= rawurlencode($item->url) ?>">Google+</a></li>
					<li><a href="http://twitter.com/home?status=<?= rawurlencode($item->title . ' ' . $item->url) ?> via @readablecc">Twitter</a></li>
				</ul>
			</div>
		</div>

		<?php if ( $item->score < 0 ): ?>
		<p class="alert">
			Based on content you voted on this article has automatically been marked as boring. If we got it wrong, press the thumbs up icon to help us understand your interests better.
		</p>
		<?php endif ?>
	</div>
</article>
<?php endforeach ?>
<?php endif ?>

<?php if ( !empty($_GET['page']) && $page = (int) abs($_GET['page']) ): ?>
<p class="pagination">
	<?php if ( $page > 1 ): ?>
	<a href="?page=<?= $page - 1 ?>">Previous page</a> &nbsp; &mdash; &nbsp;
	<?php endif ?>

	<a href="?page=<?= $page + 1 ?>">Next page</a>
</p>
<?php endif ?>
