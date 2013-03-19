<?php require 'header.html.php' ?>

<div id="page-head-wrap">
	<div id="page-head">
		<h1>Popular</h1>

		<p>
			Navigate to next and previous articles with <code>j</code> and <code>k</code>.
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
