<?php require 'header.html.php' ?>

<div class="page-header">
	<h1><?php echo $this->get('pageTitle') ?></h1>
</div>

<?php if ( $this->get('success') ): ?>
<div class="alert alert-success">
	<a class="close">&times;</a>

	<?php echo $this->get('success'); ?>
</div>
<?php endif ?>

<?php if ( $this->get('error') ): ?>
<div class="alert alert-error">
	<a class="close">&times;</a>

	<?php echo $this->get('error'); ?>
</div>
<?php endif ?>

<form id="form-feeds" method="post" action="<?php echo $this->app->getRootPath() ?>feeds" class="form-feeds form-horizontal well">
	<fieldset>
		<div id="feeds">
			<h4>Add a feed<h4>

			<div class="feed">
				<div class="control-group">
					<label class="control-label" for="name-new">Name</label>

					<div class="controls">
						<input id="name-new" name="name[new]" class="input-block-level" type="text" value="<?php echo $this->get('name-new') ?>">
					</div>
				</div>

				<div class="control-group <?php echo $this->get('error-url-new') ? 'error' : '' ?>">
					<label class="control-label" for="url-new">URL</label>

					<div class="controls">
						<input id="url-new" name="url[new]" class="input-block-level" type="text" value="<?php echo $this->get('url-new') ?>" placeholder="Website or RSS feed URL">
					</div>
				</div>

				<div class="divider"></div>
			</div>

			<?php if ( $feeds = $this->get('feeds') ): ?>
			<?php foreach ( $feeds as $feed ): ?>
			<div class="feed">
				<div class="control-group">
					<label class="control-label" for="name-<?php echo $feed->id ?>">Name</label>

					<div class="controls">
						<input id="name-<?php echo $feed->id ?>" name="name[<?php echo $feed->id ?>]" class="input-block-level" type="text" value="<?php echo $feed->name ?>">
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="url-<?php echo $feed->id ?>">URL</label>

					<div class="controls">
						<input id="url-<?php echo $feed->id ?>" name="url[<?php echo $feed->id ?>]" class="input-block-level" type="text" value="<?php echo $feed->url ?>" disabled="disabled">
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="delete-<?php echo $feed->id ?>"><i class="icon-trash"></i> Remove</button></label>

					<div class="controls">
						<input id="delete-<?php echo $feed->id ?>" name="delete[<?php echo $feed->id ?>]" type="checkbox" value="1">
					</div>
				</div>

				<div class="divider"></div>
			</div>
			<?php endforeach ?>
			<?php endif ?>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-inverse" type="submit"><i class="icon-align-justify icon-white"></i> Save feeds</button>
			</div>
		</div>
	</fieldset>
</form>

<?php require 'footer.html.php' ?>
