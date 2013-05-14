<?php require 'header.html.php' ?>

<div class="page-header">
	<h1><?php echo $this->get('pageTitle') ?></h1>
</div>

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

<form method="post" action="<?php echo $this->app->getRootPath() ?>signin" class="form-signin form-horizontal well">
	<input type="hidden" name="sessionId" value="<?php echo $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group <?php echo $this->get('error-email') ? 'error' : '' ?>">
			<label class="control-label" for="email">Email address</label>

			<div class="controls">
				<input id="email" name="email" class="input-xlarge" type="email" value="<?php echo $this->get('email') ?>">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-password') ? 'error' : '' ?>">
			<label class="control-label" for="password">Password</label>

			<div class="controls">
				<input id="password" name="password" class="input-xlarge" type="password">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit">Sign in</button>
				&nbsp; <small><a href="<?php echo $this->app->getRootPath() ?>forgot">Forgot password?</a></small>
			</div>
		</div>
	</fieldset>
</form>

<?php require 'footer.html.php' ?>
