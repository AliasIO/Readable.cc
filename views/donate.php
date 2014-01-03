<?php require 'header.php' ?>

<h1><?= $this->get('pageTitle') ?></h1>

<p>
	Thank you for your support! Your contribution will go towards hosting costs and ongoing maintenance of the site.
</p>

<p>
	Readable.cc costs roughly <strong>$100/mo</strong> to operate.
</p>

<h2>PayPal</h2>

<p>
	Suggested amount: <strong>$25</strong>
</p>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
	<input type="hidden" name="cmd" value="_donations">
	<input type="hidden" name="business" value="info@readable.cc">
	<input type="hidden" name="lc" value="US">
	<input type="hidden" name="item_name" value="Readable.cc">
	<input type="hidden" name="no_note" value="0">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">

	<input type="image" src="<?= $this->app->getRootPath() ?>images/paypal.png" name="submit" alt="PayPal — The safer, easier way to pay online.">
</form>

<h2>Bitcoin</h2>

<p>
	<img src="<?= $this->app->getRootPath() ?>images/bitcoinqr.png" width="186" height="186"><br>

	<small>1PHiJyVvsVYJwFuBSAW9NeppbHkz6NjmBp</small>
</p>

<?php require 'footer.php' ?>
