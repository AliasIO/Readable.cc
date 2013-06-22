<?php require 'header.php' ?>

<div class="page-header">
	<h1><?= $this->get('pageTitle') ?></h1>
</div>

<?php if ( $this->get('success') ): ?>
<div class="alert alert-success">
	<?= $this->get('success', true); ?>
</div>
<?php endif ?>

<?php if ( $this->get('error') ): ?>
<div class="alert alert-error">
	<?= $this->error; ?>
</div>
<?php endif ?>

<form id="form-signin" method="post" action="<?= $this->app->getRootPath() ?>signin" class="well">
	<input type="hidden" name="sessionId" value="<?= $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group <?= $this->get('error-email') ? 'error' : '' ?>">
			<label class="control-label" for="email">Email address</label>

			<div class="controls">
				<input id="email" name="email" type="email" value="<?= $this->get('email') ?>">
			</div>
		</div>

		<div class="control-group <?= $this->get('error-password') ? 'error' : '' ?>">
			<label class="control-label" for="password">Password</label>

			<div class="controls">
				<input id="password" name="password" type="password">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit">Sign in</button>
				&nbsp; <small><a href="<?= $this->app->getRootPath() ?>forgot">Forgot password?</a></small>
			</div>
		</div>
	</fieldset>
</form>

<?php require 'footer.php' ?>
