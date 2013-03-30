<?php $items = $this->get('items', false) ?>

<?php if ( !$items ): ?>
<div id="items-footer">
	<p>
		<i class="entypo chevron-small-left"></i> No more unread articles <i class="entypo chevron-small-right"></i>
	</p>
</div>
<?php endif ?>

<?php foreach ( $items as $item ): ?>
<article
	data-item-id="<?php    echo $item->id                     ?>"
	data-item-score="<?php echo $item->score                  ?>"
	data-item-url="<?php   echo $this->htmlEncode($item->url) ?>"
	class="inactive collapsed <?php echo $item->score < 0 ? ' boring' : '' ?>"
	>
	<h1<?php echo $item->score < 0 ? ' title="You may find this article uninteresting (based on articles you voted on)"' : '' ?>>
		<a href="<?php echo $this->htmlEncode($item->url) ?>"><?php echo $item->title ?></a>
	</h1>

	<p class="item-date">
		<em>
			<i class="entypo book"></i>
			By <strong><a href="/feed/view/<?php echo $item->feed_id ?>" title="<?php echo parse_url($item->feed_link,  PHP_URL_HOST) ?>"><?php echo $item->feed_title ?></a></strong>
			<?php echo $item->posted_at ? 'on ' . date('F j, Y', $item->posted_at) : '' ?>
			<span class="feed-options">
				<!--(score: <?php echo number_format($item->score) ?>)-->
				&mdash;
				<?php if ( $this->get('controller') == 'index' ): ?>
				<a href="/report/article/<?php echo $item->id ?>" class="report" data-feed-id="<?php echo $item->feed_id ?>" title="Report inappropriate content">
					<i class="entypo flag"></i> Report
				</a>
				&nbsp;
				<?php endif ?>
				<a href="javascript: void(0);" class="subscription <?php echo $item->feed_subscribed ? 'unscubscribe' : 'subscribe' ?>" data-feed-id="<?php echo $item->feed_id ?>" title="Subscriptions appear in &lsquo;My Reading&rsquo;">
					<?php if ( $item->feed_subscribed ): ?>
					<i class="entypo squared-minus"></i> Unsubscribe
					<?php else: ?>
					<i class="entypo squared-plus"></i> Subscribe
					<?php endif ?>
				</a>
			</span>
		</em>
	</p>

	<div class="item-wrap">
		<div class="item-contents">
			<?php echo $item->contents ?>
		</div>

		<div style="clear: both;"></div>

		<p class="article-buttons">
			<button class="btn btn-small item-vote<?php echo $item->vote == 1 ? ' btn-inverse voted' : '' ?>" data-item-id="<?php echo $item->id ?>" data-vote="1"  title="Promote articles like these in &lsquo;My Reading&rsquo;">
				<i class="entypo thumbs-up"></i>
				Interesting
			</button>

			<button class="btn btn-small item-vote<?php echo $item->vote == -1 ? ' btn-inverse voted' : '' ?>" data-item-id="<?php echo $item->id ?>" data-vote="-1" title="Demote articles like these in &lsquo;My Reading&rsquo;">
				<i class="entypo thumbs-down"></i>
				Boring
			</button>

			<button class="btn btn-small item-save<?php echo $item->saved ? ' btn-inverse saved' : '' ?>" data-item-id="<?php echo $item->id ?>" title="Save this article to read later">
				<i class="entypo install"></i>
				Save<?php echo $item->saved ? 'd' : '' ?>
			</button>
		</p>
	</div>
</article>
<?php endforeach ?>
