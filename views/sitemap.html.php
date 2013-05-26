<?php echo '<?xml version="1.0" encoding="UTF-8"?>' ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
	<?php foreach ( $this->get('feeds') as $feed ): ?>
	<url>
		<loc><?php echo $this->app->getConfig('websiteUrl') . $this->app->getSingleton('helper')->getFeedLink($feed->id, $feed->title) ?></loc>
		<priority>0.8</priority>
	</url>
	<?php endforeach ?>
	<?php foreach ( $this->get('folders') as $folder ): ?>
	<url>
		<loc><?php echo $this->app->getConfig('websiteUrl') . $this->app->getSingleton('helper')->getFolderLink($folder->id, $folder->title) ?></loc>
		<priority>0.6</priority>
	</url>
	<?php endforeach ?>
</urlset>
