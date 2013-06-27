<?php require 'header.php' ?>

<h1><?= $this->get('pageTitle') ?></h1>

<h2>Personal information</h2>

<p>
	We do not store any personal information such as your name and address. However your IP address may be stored and associated with your account.
</p>

<p>
	When you perform a payment your personal details are shared with our payment processor, <a href="https://pin.net.au">Pin Payments</a>.
</p>

<p>
	At any time you may delete your account and all associated records.
</p>

<h2>Email</h2>

<p>
	We do not send out marketing emails. Your email address will not be shared with anyone.
</p>

<h2>Security</h3>

<p>
	Your password is salted and hashed using the blowfish encryption algorithm.
</p>

<p>
	Personal information is only ever transmitted over a secure connection (SSL).
</p>

<h2>Openness</h2>

<p>
	<?= $this->app->getConfig('siteName') ?> is free and <a href="<?= $this->app->getConfig('repoUrl') ?>">open source</a>.
</p>

<?php require 'footer.php' ?>
