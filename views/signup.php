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
	<?= $this->app->getConfig('siteName') ?> is a free <a href="https://en.wikipedia.org/wiki/News_aggregator">news reader</a>.
</p>

<p>
	Access your favourite blogs and news sites from one place, optimised for distraction free reading.
</p>

<p>
	Sign up to manage your own <a href="https://en.wikipedia.org/wiki/Web_feed">feeds</a> and vote on articles. Articles we believe you'll find interesting are promoted to the top of your reading list.
</p>

<form id="form-signup" method="post" action="<?= $this->app->getRootPath() ?>signup" class="form-horizontal well">
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

		<div class="control-group <?= $this->get('error-password-repeat') ? 'error' : '' ?>">
			<label class="control-label" for="password-repeat">Repeat password</label>

			<div class="controls">
				<input id="password" name="password-repeat" type="password">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit">Create account</button>
			</div>
		</div>
	</fieldset>
</form>

<?php require 'footer.php' ?>
