<?php foreach ( $this->get('items') as $item ): ?>
<article data-item-id="<?php echo $item->id ?>" class="inactive<?php echo $item->score < 0 ? ' collapsed' : '' ?>">
	<h1><a href="<?php echo $item->url ?>"><?php echo $item->title ?></a></h1>

	<div class="item-wrap">
		<p class="item-date">
			<em>
				<i class="entypo book"></i>
				Posted by <a href="<?php echo $item->feed_link ?>"><?php echo $item->feed_title ?></a>
				on <?php echo date('F j, Y', $item->posted_at) ?>
				<!--(score: <?php echo number_format($item->score) ?>)-->
				&mdash; <a href="javascript: void(0);" class="subscription <?php echo $item->feed_subscribed ? 'unscubscribe' : 'subscribe' ?>" data-feed-id="<?php echo $item->feed_id ?>">
					<?php if ( $item->feed_subscribed ): ?>
					<i class="entypo squared-minus"></i> Unsubscribe
					<?php else: ?>
					<i class="entypo squared-plus"></i> Subscribe
					<?php endif ?>
				</a>
			</em>
		</p>

		<?php echo $this->htmlDecode($item->contents) ?>

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

			<button class="btn btn-small item-save<?php echo $item->saved ? ' btn-inverse save' : '' ?>" data-item-id="<?php echo $item->id ?>">
				<i class="entypo install"></i>
				Save<?php echo $item->saved ? 'd' : '' ?>
			</button>
		</p>
	</div>
</article>
<?php endforeach ?>
