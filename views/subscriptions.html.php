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
	<?php echo $this->get('error', false); ?>
</div>
<?php endif ?>

<?php if ( $feeds = $this->get('feeds') ): ?>
<h3>Subscriptions</h3>

<ul id="subscriptions">
	<?php foreach ( $feeds as $feed ): ?>
	<li>
		<strong><a href="<?php echo $feed->link ?>"><?php echo $feed->title ?></a></strong> &mdash;
		<small>
			<a href="<?php echo $feed->url ?>"><?php echo parse_url($feed->url, PHP_URL_HOST) ?></a>
			<a class="unsubscribe" href="javascript: void(0);" data-feed-id="<?php echo $feed->id ?>" data-feed-name="<?php echo $feed->title ?>">
				<i class="entypo squared-minus"></i> Unsubscribe
			</a>
		</small>
	</li>
	<?php endforeach ?>
</ul>
<?php endif ?>

<h3>Add a subscription</h3>

<form id="form-subscriptions-subscribe" method="post" action="/subscriptions" class="form-subscriptions form-horizontal well">
	<input type="hidden" name="form" value="subscribe">
	<input type="hidden" name="sessionId" value="<?php echo $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group <?php echo $this->get('error-url') ? 'error' : '' ?>">
			<label class="control-label" for="url">URL</label>

			<div class="controls">
				<input id="url" name="url" class="input-block-level" type="text" value="<?php echo $this->get('url') ?>" placeholder="Website or feed URL">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit"><i class="entypo rss"></i> Add feed</button>
			</div>
		</div>
	</fieldset>
</form>

<h3>Import & export</h3>

<form id="form-subscriptions-import" method="post" action="/subscriptions" class="form-horizontal well" enctype="multipart/form-data">
	<input type="hidden" name="form" value="import">
	<input type="hidden" name="sessionId" value="<?php echo $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group">
			<label class="control-label" for="file">OPML File</label>

			<div class="controls">
				<input id="file" name="file" class="input-block-level" type="file">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit"><i class="entypo rss"></i> Import feeds</button>
			</div>
		</div>
	</fieldset>
</form>

<script>
	readable.subscriptions.init();
</script>

<?php require 'footer.html.php' ?>
