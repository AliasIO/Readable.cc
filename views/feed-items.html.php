<?php foreach ( $this->get('items') as $item ): ?>
<article data-item-id="<?php echo $item->id ?>" class="inactive<?php echo $item->score < 0 ? ' collapsed' : '' ?>">
	<h1><a href="<?php echo $item->url ?>"><?php echo $item->title ?></a></h1>

	<div class="item-wrap">
		<p class="item-date">
			<em>Posted <?php echo $item->feed_name ? ' by ' . $item->feed_name : '' ?> on <?php echo date('F j, Y', strtotime($item->posted_at)) ?> (score: <?php echo number_format($item->score) ?>)</em>
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

			<label for="unread-<?php echo $item->id ?>">
				<input class="keep-unread" type="checkbox" id="unread-<?php echo $item->id ?>" data-item-id="<?php echo $item->id ?>" value="1">
				Keep unread
			</label>
		</p>
	</div>
</article>
<?php endforeach ?>
