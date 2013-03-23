<?php require 'header.html.php' ?>

<div id="page-head-wrap">
	<div id="page-head">
		<h1>Popular Reading</h1>

		<p>
			Navigate to next and previous articles with <code>j</code> and <code>k</code>.
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

<script>
	readable.items.init();
</script>
<?php endif ?>

<?php require 'footer.html.php' ?>
