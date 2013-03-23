<?php $items = $this->get('items', false) ?>

<?php if ( !$items ): ?>
<div id="items-footer">
	<p>
		<i class="entypo chevron-small-left"></i> No more unread articles <i class="entypo chevron-small-right"></i>
	</p>
</div>
<?php endif ?>

<?php foreach ( $items as $item ): ?>
<article data-item-id="<?php echo $item->id ?>" data-item-score="<?php echo $item->score ?>" class="inactive collapsed <?php echo $item->score < 0 ? ' boring' : '' ?>">
	<h1<?php echo $item->score < 0 ? ' title="You may find this article uninteresting (based on articles you voted on)"' : '' ?>>
		<a href="<?php echo $item->url ?>"><?php echo $item->title ?></a>
	</h1>

	<p class="item-date">
		<em>
			<i class="entypo book"></i>
			By <strong><a href="/feed/read/<?php echo $item->feed_id ?>"><?php echo $item->feed_title ?></a></strong>
			<?php echo $item->posted_at ? 'on ' . date('F j, Y', $item->posted_at) : '' ?>
			<span class="feed-options">
				<!--(score: <?php echo number_format($item->score) ?>)-->
				&mdash; <a href="javascript: void(0);" class="subscription <?php echo $item->feed_subscribed ? 'unscubscribe' : 'subscribe' ?>" data-feed-id="<?php echo $item->feed_id ?>">
					<?php if ( $item->feed_subscribed ): ?>
					<i class="entypo squared-minus"></i> Unsubscribe
					<?php else: ?>
					<i class="entypo squared-plus"></i> Subscribe
					<?php endif ?>
				</a>
			<span>
		</em>
	</p>

	<div class="item-wrap">
		<div class="item-contents">
			<?php echo $item->contents ?>
		</div>

		<div style="clear: both;"></div>

		<p class="article-buttons">
			<button class="btn btn-small item-vote<?php echo $item->vote == 1 ? ' btn-inverse voted' : '' ?>" data-item-id="<?php echo $item->id ?>" data-vote="1" >
				<i class="entypo thumbs-up"></i>
				Interesting
			</button>

			<button class="btn btn-small item-vote<?php echo $item->vote == -1 ? ' btn-inverse voted' : '' ?>" data-item-id="<?php echo $item->id ?>" data-vote="-1">
				<i class="entypo thumbs-down"></i>
				Boring
			</button>

			<button class="btn btn-small item-save<?php echo $item->saved ? ' btn-inverse saved' : '' ?>" data-item-id="<?php echo $item->id ?>">
				<i class="entypo install"></i>
				Save<?php echo $item->saved ? 'd' : '' ?>
			</button>
		</p>
	</div>
</article>
<?php endforeach ?>
