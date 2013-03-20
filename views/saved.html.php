<?php require 'header.html.php' ?>

<div id="page-head-wrap">
	<div id="page-head">
		<h1>Saved</h1>

		<p>
			Click &lsquo;save&rsquo; on articles you wish to read later.
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
