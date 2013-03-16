<?php require 'header.html.php' ?>

<div class="alert alert-float alert-sticky">
	<h4>There is nothing here&hellip; Yet.</h4>

	<p>
		Head over to the <a href="<?php echo $this->app->getRootPath() ?>subscriptions">Subscriptions</a> page, add some feeds and check back shortly!
	</p>

	<p>
		You may also subscribe to feeds from the <a href="<?php echo $this->app->getRootPath() ?>">Popular</a> page.
	</p>
</div>

<div id="items"></div>

<script>
	readable.items.init();
</script>

<?php require 'footer.html.php' ?>
