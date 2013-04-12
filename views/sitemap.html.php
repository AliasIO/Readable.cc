<?php echo '<?xml version="1.0" encoding="UTF-8"?>' ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
	<?php foreach ( $this->get('feeds') as $feed ): ?>
	<url>
		<loc><?php echo $this->app->getConfig('websiteUrl') ?>/feed/view/<?php echo $feed->id ?></loc>
	</url>
	<?php endforeach ?>
</urlset>
