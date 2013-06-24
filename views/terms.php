<?php require 'header.php' ?>

<h1><?= $this->get('pageTitle') ?></h1>

<p>
	<a href="<?= $this->app->getRootPath() ?>"><?= $this->app->getConfig('siteName') ?></a> is an Australian business selling digital goods. Transactions are billed in Australian dollars unless noted otherwise.
</p>

<p>
	<small>
		&nbsp; <?= implode('<br>&nbsp; ', $this->businessDetails) ?>
	</small>
</p>

<h2>Refund policy</h2>

<p>
	If you would like to request a refund, please <a href="<?= $this->app->getRootPath() ?>help#contact">contact us by email</a>. Note that we may not be legally required to provide a refund.
</p>

<?php require 'footer.php' ?>
