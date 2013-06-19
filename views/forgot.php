<?php require 'header.php' ?>

<h1><?= $this->get('pageTitle') ?></h1>

<?php if ( $this->get('success') ): ?>
<div class="alert alert-success">
	<?= $this->get('success'); ?>
</div>
<?php endif ?>

<?php if ( $this->get('error') ): ?>
<div class="alert alert-error">
	<?= $this->get('error'); ?>
</div>
<?php endif ?>

<p>
	If you have trouble accessing your account please send an email to <a class="contact-email" href="<?= $this->app->getConfig('emailHoneyPot') ?>"><?= $this->app->getConfig('emailHoneyPot') ?></a> for assistance.
</p>

<form id="form-forgot" method="post" action="<?= $this->app->getRootPath() ?>forgot" class="well">
	<input type="hidden" name="sessionId" value="<?= $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group <?= $this->get('error-email') ? 'error' : '' ?>">
			<label class="control-label" for="email">Email address</label>

			<div class="controls">
				<input id="email" name="email" class="input-xlarge" type="email" value="<?= $this->get('email') ?>">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit">Request password</button>
			</div>
		</div>
	</fieldset>
</form>

<?php require 'footer.php' ?>
