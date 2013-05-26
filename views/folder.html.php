<?php require 'header.html.php' ?>

<div id="page-head-wrap">
	<div id="page-head">
		<h1>Folder: <?php echo $this->get('title') ?></h1>

		<p>
			<?php if ( $this->app->getSingleton('session')->get('id') ): ?>
			<span>Folders are public and anonymous, share the link with anyone!</span>
			<?php else: ?>
			<span><a href="<?php echo $this->app->getRootPath() ?>signup">Sign up</a> to manage feeds and create folders of your own.</span>
			<?php endif ?>
		</p>
	</div>
</div>

<?php if ( $this->app->getAction() == 'view' && !$this->get('items', false) ): ?>
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
