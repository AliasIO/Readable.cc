<?php require 'header.html.php' ?>

<div id="page-head-wrap">
	<div id="page-head">
		<h1><?php echo $this->get('title') ?></h1>

		<p>
			<a href="<?php echo $this->get('link') ?>"><?php echo parse_url($this->get('link'), PHP_URL_HOST) ?></a>
		</p>
	</div>
</div>

<div id="items-read-line"></div>

<div id="items">
	<?php require 'read.html.php' ?>
</div>

<script>
	readable.items.init();
</script>

<?php require 'footer.html.php' ?>
