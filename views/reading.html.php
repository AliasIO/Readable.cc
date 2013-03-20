<?php require 'header.html.php' ?>

<div id="page-head-wrap">
	<div id="page-head">
		<h1>My Reading</h1>

		<p><a href="/subscriptions">Subscribe</a> to feeds and vote on articles for personalised reading.</p>
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
