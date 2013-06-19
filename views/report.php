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
	Please report any content that you feel is inappropriate for this website.
</p>

<form id="form-report" method="post" action="<?= $this->app->getRootPath() ?>report/article/<?= $this->get('itemId') ?>" class="well">
	<input type="hidden" name="sessionId" value="<?= $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group">
			<label class="control-label" for="reason">Reason</label>

			<div class="controls">
				<textarea id="reason" name="reason" class="input-xlarge" placeholder="E.g. adult content"></textarea>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="email">Email address</label>

			<div class="controls">
				<input id="email" name="email" class="input-xlarge" type="email" value="<?= $this->get('email') ?>">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit">Send report</button>
			</div>
		</div>
	</fieldset>
</form>

<?php require 'footer.php' ?>
