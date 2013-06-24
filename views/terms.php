<?php require 'header.php' ?>

<h1><?= $this->get('pageTitle') ?></h1>

<p>
	<small>
		&nbsp; <?= implode('<br>&nbsp; ', $this->businessDetails) ?>
	</small>
</p>

<?php require 'footer.php' ?>
