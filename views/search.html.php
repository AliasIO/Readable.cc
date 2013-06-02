<?php require 'header.html.php' ?>

<div id="page-head-wrap">
	<div id="page-head">
		<h1>Search articles</h1>

		<form id="search" method="get">
			<input type="text" id="query" name="query" value="<?php echo $this->get('query') ?>">

			<button type="submit">Search</button>
		</form>
	</div>
</div>

<?php if ( ( $this->app->getAction() == 'index' || $this->app->getAction() == 'query' ) && !$this->get('items', false) ): ?>
<div id="items-footer">
	<?php if ( $this->get('query') ): ?>
	<p>
		<i class="entypo chevron-small-left"></i> Your query yielded no results <i class="entypo chevron-small-right"></i>
	</p>
	<?php endif ?>
</div>
<?php else: ?>
<div id="items-read-line"></div>

<div id="items">
	<?php require 'read.html.php' ?>
</div>
<?php endif ?>

<?php require 'footer.html.php' ?>
