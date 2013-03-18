<?php foreach ( $this->get('items') as $item ): ?>
<article data-item-id="<?php echo $item->id ?>" class="inactive<?php echo $item->score < 0 ? ' collapsed' : '' ?>">
	<h1><a href="<?php echo $item->url ?>"><?php echo $item->title ?></a></h1>

	<div class="item-wrap">
		<p class="item-date">
			<em>
				<i class="icon-bookmark"></i>
				Posted by <a href="<?php echo $item->feed_link ?>"><?php echo $item->feed_title ?></a>
				on <?php echo date('F j, Y', $item->posted_at) ?>
				<!--(score: <?php echo number_format($item->score) ?>)-->
				&mdash; <a href="javascript: void(0);" class="subscription <?php echo $item->feed_subscribed ? 'unscubscribe' : 'subscribe' ?>" data-feed-id="<?php echo $item->feed_id ?>">
					<?php if ( $item->feed_subscribed ): ?>
					<i class="icon-minus-sign"></i> Unsubscribe
					<?php else: ?>
					<i class="icon-plus-sign"></i> Subscribe
					<?php endif ?>
				</a>
			</em>
		</p>

		<?php echo $this->htmlDecode($item->contents) ?>

		<div style="clear: both;"></div>

		<p class="article-buttons">
			<button class="btn btn-small item-vote<?php echo $item->vote == 1 ? ' btn-inverse voted' : '' ?>" data-item-id="<?php echo $item->id ?>" data-vote="1" >
				<i class="icon-thumbs-up<?php echo $item->vote == 1 ? ' icon-white' : '' ?>"></i>
				Interesting
			</button>

			<button class="btn btn-small item-vote<?php echo $item->vote == -1 ? ' btn-inverse voted' : '' ?>" data-item-id="<?php echo $item->id ?>" data-vote="-1">
				<i class="icon-thumbs-down<?php echo $item->vote == -1 ? ' icon-white' : '' ?>"></i>
				Boring
			</button>

			<?php if ( $this->app->getSingleton('session')->get('id') ): ?>
			<label for="unread-<?php echo $item->id ?>">
				<input class="keep-unread" type="checkbox" id="unread-<?php echo $item->id ?>" data-item-id="<?php echo $item->id ?>" value="1">
				Keep unread
			</label>

			<label for="save-<?php echo $item->id ?>">
				<input class="save" type="checkbox" id="save-<?php echo $item->id ?>" data-item-id="<?php echo $item->id ?>" value="1"<?php echo $item->saved ? ' checked="checked"' : '' ?>>
				Save
			</label>
			<?php endif ?>
		</p>
	</div>
</article>
<?php endforeach ?>
