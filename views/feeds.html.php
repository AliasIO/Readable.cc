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

<form id="form-feeds" method="post" action="<?php echo $this->app->getRootPath() ?>feeds" class="form-feeds form-horizontal well">
	<legend>Add a new feed</legend>

	<fieldset>
		<div class="control-group">
			<label class="control-label" for="name">Name</label>

			<div class="controls">
				<input id="name" name="name" class="input-block-level" type="text" value="<?php echo $this->get('name') ?>">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-url') ? 'error' : '' ?>">
			<label class="control-label" for="url-new">URL</label>

			<div class="controls">
				<input id="url" name="url" class="input-block-level" type="text" value="<?php echo $this->get('url') ?>" placeholder="Website or RSS feed URL">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit" name="submit-new"><i class="icon-align-justify icon-white"></i> Add feed</button>
			</div>
		</div>
	</fieldset>
</form>

<form id="form-feeds" method="post" action="<?php echo $this->app->getRootPath() ?>feeds" class="form-feeds form-horizontal well">
	<legend>Import OPML</legend>

	<fieldset>
		<div class="control-group">
			<label class="control-label" for="file">File</label>

			<div class="controls">
				<input id="file" name="file" class="input-block-level" type="file">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit" name="submit-import"><i class="icon-align-justify icon-white"></i> Import feeds</button>
			</div>
		</div>
	</fieldset>
</form>

<?php if ( $feeds = $this->get('feeds') ): ?>
<form id="form-feeds" method="post" action="<?php echo $this->app->getRootPath() ?>feeds" class="form-feeds form-horizontal well">
	<legend>All feeds</legend>

	<?php foreach ( $feeds as $feed ): ?>
	<fieldset>
		<div class="control-group">
			<label class="control-label" for="name-<?php echo $feed->id ?>">Name</label>

			<div class="controls">
				<input id="name-<?php echo $feed->id ?>" name="name[<?php echo $feed->id ?>]" class="input-block-level" type="text" value="<?php echo $feed->name ?>">
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">URL</label>

			<div class="controls">
				<input class="input-block-level" type="text" value="<?php echo $feed->url ?>" disabled="disabled">
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="delete-<?php echo $feed->id ?>"><i class="icon-trash"></i> Remove</button></label>

			<div class="controls">
				<input id="delete-<?php echo $feed->id ?>" name="delete[<?php echo $feed->id ?>]" type="checkbox" value="1">
			</div>
		</div>
	</fieldset>
	<?php endforeach ?>

	<fieldset>
		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit" name="submit-existing"><i class="icon-align-justify icon-white"></i> Save feeds</button>
			</div>
		</div>
	</fieldset>
</form>
<?php endif ?>

<?php require 'footer.html.php' ?>
