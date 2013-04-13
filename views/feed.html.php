<?php require 'header.html.php' ?>

<div id="page-head-wrap">
	<div id="page-head">
		<h1><?php echo $this->get('title') ?></h1>

		<p>
			<a href="<?php echo $this->get('link') ?>"><?php echo parse_url($this->get('link'), PHP_URL_HOST) ?></a>
		</p>
	</div>
</div>

<?php if ( $this->app->getAction() == 'read' && !$this->get('items', false) ): ?>
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
