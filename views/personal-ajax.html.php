<?php foreach ( $this->get('items') as $item ): ?>
<article>
	<h1><a href="<?php echo $item->url ?>"><?php echo $item->title ?></a></h1>

	<p class="article-date">
		<em>Posted <?php echo $item->feed_name ? ' by ' . $item->feed_name : '' ?> on <?php echo date('F j, Y', strtotime($item->posted_at)) ?></em>
	</p>

	<?php echo $this->htmlDecode($item->contents) ?>

	<p class="article-buttons">
		<a class="btn btn-small"><i class="icon-thumbs-up"></i> Show me more like this</a>
		<a class="btn btn-small"><i class="icon-thumbs-down"></i> Hide articles like these</a>
	</p>
</article>
<?php endforeach ?>
