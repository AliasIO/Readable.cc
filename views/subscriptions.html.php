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

<table id="subscriptions" class="table table-bordered table-striped table-hover">
	<tbody>
		<?php foreach ( $feeds as $feed ): ?>
		<tr>
			<td>
				<a href="<?php echo $this->app->getSingleton('helper')->getFeedLink($feed->id, $feed->title) ?>"><?php echo $feed->title ?></a>
				<span>
					<a href="<?php echo $feed->link ?>" title="Visit the website at <?php echo parse_url($feed->link, PHP_URL_HOST) ?>"><i class="entypo link"></i></a>
					<a href="<?php echo $feed->url  ?>" title="View the feed at <?php echo parse_url($feed->url,  PHP_URL_HOST) ?>"><i class="entypo rss"></i></a>
					<small>
						&nbsp;
						<em><?php echo $feed->last_fetched_at ? 'Last fetched on ' . date('F j, Y', $feed->last_fetched_at) : 'Never successfully fetched' ?></em>
						&nbsp;
					</small>
				</span>
			</td>
			<td>
				<button class="btn btn-small unsubscribe" data-feed-id="<?php echo $feed->id ?>" data-feed-name="<?php echo $feed->title ?>">
					<i class="entypo squared-minus"></i>&nbsp;Unsubscribe
				</button>
			</td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>

<div class="divider"></div>
<?php else: ?>
<h3>Get started</h3>

<p>
	Add a few subscriptions to get started. Articles appear in &lsquo;<a href="/reading">My Reading</a>&rsquo;.
</p>

<table id="suggestions" class="table table-bordered table-striped table-hover">
	<tbody>
		<tr>
			<th>World News</th>
			<td><a href="https://www.nytimes.com/">The New York Times</a></td>
			<td><button class="subscribe btn btn-small" data-url="http://rss.nytimes.com/services/xml/rss/nyt/GlobalHome.xml">
				<i class="entypo squared-plus"></i>&nbsp;Subscribe</button>
			</td>
		</tr>
		<tr>
			<th><br></th>
			<td><a href="http://boston.com/">Boston.com</a></td>
			<td><button class="subscribe btn btn-small" data-url="http://feeds.boston.com/boston/topstories">
				<i class="entypo squared-plus"></i>&nbsp;Subscribe</button>
			</td>
		</tr>
		<tr>
			<th>Technology</th>
			<td><a href="https://www.techdirt.com/">Techdirt</a></td>
			<td><button class="subscribe btn btn-small" data-url="http://feeds.feedburner.com/techdirt/feed">
				<i class="entypo squared-plus"></i>&nbsp;Subscribe</button>
			</td>
		</tr>
		<tr>
			<th><br></th>
			<td><a href="http://thenextweb.com/">The Next Web</a></td>
			<td><button class="subscribe btn btn-small" data-url="http://feeds2.feedburner.com/thenextwebtopstories">
				<i class="entypo squared-plus"></i>&nbsp;Subscribe</button>
			</td>
		</tr>
		<tr>
			<th>Science</th>
			<td><a href="http://arstechnica.com/science/">Ars Technica - Scientific Method</a></td>
			<td><button class="subscribe btn btn-small" data-url="http://feeds.arstechnica.com/arstechnica/science?format=xml">
				<i class="entypo squared-plus"></i>&nbsp;Subscribe</button>
			</td>
		</tr>
		<tr>
			<th><br></th>
			<td><a href="http://www.nasa.gov">NASA Breaking News</a></td>
			<td><button class="subscribe btn btn-small" data-url="http://www.nasa.gov/rss/breaking_news.rss">
				<i class="entypo squared-plus"></i>&nbsp;Subscribe</button>
			</td>
		</tr>
		<tr>
			<th>Comics</th>
			<td><a href="https://xkcd.com/">xkcd</a></td>
			<td><button class="subscribe btn btn-small" data-url="https://xkcd.com/rss.xml">
				<i class="entypo squared-plus"></i>&nbsp;Subscribe</button>
			</td>
		</tr>
		<tr>
			<th><br></th>
			<td><a href="http://pbfcomics.com/">The Perry Bible Fellowship</a></td>
			<td><button class="subscribe btn btn-small" data-url="http://pbfcomics.com/feed/feed.xml">
				<i class="entypo squared-plus"></i>&nbsp;Subscribe</button>
			</td>
		</tr>
		<tr>
			<th>Sports</th>
			<td><a href="http://sports.yahoo.com/">Yahoo! Sports</a></td>
			<td><button class="subscribe btn btn-small" data-url="http://news.yahoo.com/rss/sports">
				<i class="entypo squared-plus"></i>&nbsp;Subscribe</button>
			</td>
		</tr>
		<tr>
			<th><br></th>
			<td><a href="http://espn.go.com/">ESPN</a></td>
			<td><button class="subscribe btn btn-small" data-url="http://sports.espn.go.com/espn/rss/news">
				<i class="entypo squared-plus"></i>&nbsp;Subscribe</button>
			</td>
		</tr>
		<tr>
			<th>Entertainment</th>
			<td><a href="http://www.bbc.com/news/entertainment_and_arts/">BBC News - Entertainment &amp; Arts</a></td>
			<td><button class="subscribe btn btn-small" data-url="http://feeds.bbci.co.uk/news/entertainment_and_arts/rss.xml">
				<i class="entypo squared-plus"></i>&nbsp;Subscribe</button>
			</td>
		</tr>
		<tr>
			<th><br></th>
			<td><a href="https://www.google.com/news/section?topic=e">Google News - Entertainment</a></td>
			<td><button class="subscribe btn btn-small" data-url="https://www.google.com/news?pz=1&cf=all&ned=au&hl=en&topic=e&output=rss">
				<i class="entypo squared-plus"></i>&nbsp;Subscribe</button>
			</td>
		</tr>
	</tbody>
</table>

<div class="divider"></div>
<?php endif ?>

<h3>Subscribe to feed</h3>

<p>
	Specify a URL to a <a href="https://en.wikipedia.org/wiki/RSS">RSS</a> or <a href="https://en.wikipedia.org/wiki/Atom_%28standard%29">Atom</a> feed to subscribe.
	Alternatively you can just use a website's URL and we'll try to find a feed automatically.
</p>

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
				<button class="btn btn-primary" type="submit"><i class="entypo rss"></i> Subscribe</button><div class="loading"></div><span class="message"></span>
			</div>
		</div>
	</fieldset>
</form>

<div class="divider"></div>

<h3>Import & export feeds</h3>

<p>
	It may take several hours for imported feeds to appear in &lsquo;<a href="/reading">My Reading</a>&rsquo;.
</p>

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

	<fieldset>
		<div class="control-group">
			<div class="controls">
				<a class="btn btn-primary" href="/subscriptions/export"><i class="entypo rss"></i> Export feeds</a>
			</div>
		</div>
	</fieldset>
</form>

<?php require 'footer.html.php' ?>
