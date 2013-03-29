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

<p>
	Please report any content that you feel is inappropriate for this website.
</p>

<form method="post" action="/report/article/<?php echo $this->get('itemId') ?>" class="form-report form-horizontal well">
	<input type="hidden" name="sessionId" value="<?php echo $this->app->getSingleton('session')->getId() ?>">

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
				<input id="email" name="email" class="input-xlarge" type="email" value="<?php echo $this->get('email') ?>">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit"><i class="entypo flag"></i> Send report</button>
			</div>
		</div>
	</fieldset>
</form>

<?php require 'footer.html.php' ?>
