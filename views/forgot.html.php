<?php require 'header.html.php' ?>

<h1><?php echo $this->get('pageTitle') ?></h1>

<?php if ( $this->get('success') ): ?>
<div class="alert alert-success">
	<?php echo $this->get('success'); ?>
</div>
<?php endif ?>

<?php if ( $this->get('error') ): ?>
<div class="alert alert-error">
	<?php echo $this->get('error'); ?>
</div>
<?php endif ?>

<p>
	If you have trouble accessing your account please send an email to <a class="contact-email" href="<?php echo $this->app->getConfig('emailHoneyPot') ?>"><?php echo $this->app->getConfig('emailHoneyPot') ?></a> for assistance.
</p>

<form id="form-forgot" method="post" action="<?php echo $this->app->getRootPath() ?>forgot" class="well">
	<input type="hidden" name="sessionId" value="<?php echo $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group <?php echo $this->get('error-email') ? 'error' : '' ?>">
			<label class="control-label" for="email">Email address</label>

			<div class="controls">
				<input id="email" name="email" class="input-xlarge" type="email" value="<?php echo $this->get('email') ?>">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit">Request password</button>
			</div>
		</div>
	</fieldset>
</form>

<?php require 'footer.html.php' ?>
