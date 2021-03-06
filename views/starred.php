<?php require 'header.php' ?>

<div id="page-head-wrap">
	<div id="page-head">
		<h1>Starred</h1>

		<p>
			<span>Click the star icon on articles you wish to read later.</span>
		</p>
	</div>
</div>

<?php if ( $this->app->getAction() == 'index' && !$this->get('items', false) ): ?>
<div id="items-footer">
	<p>
		<i class="entypo chevron-small-left"></i> You don&lsquo;t have any starred articles <i class="entypo chevron-small-right"></i>
	</p>
</div>
<?php else: ?>
<div id="items-read-line"></div>

<div id="items">
	<?php require 'read.php' ?>
</div>
<?php endif ?>

<?php require 'footer.php' ?>
