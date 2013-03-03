<?php require 'header.html.php' ?>

<?php if ( !$this->app->getSingleton('session')->get('id') ): ?>
<div class="alert alert-block">
	<p>
		<a href="<?php echo $this->app->getRootPath() ?>signup">Sign up</a> to manage your own RSS feeds. Readable filters content automagically to suit your interests.
	</p>
</div>
<?php endif ?>

<article>
	<h1><?php echo $this->get('pageTitle') ?></h1>

	<p>
		<em><a href="">alias.io</a></em>
	</p>

	<p>
		Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam eu lorem quam, sit amet tincidunt mauris. Praesent tincidunt felis vitae justo rhoncus viverra. Fusce non mauris vitae diam consequat blandit eu quis enim. Donec id ante tortor. Ut porta, urna eget vulputate sollicitudin, justo sem sodales enim, eu varius ligula libero id magna. Sed eu odio non lorem porta scelerisque. Nunc eu orci id neque pellentesque consequat vitae gravida nisl. Suspendisse diam sem, tristique eu volutpat in, luctus vitae odio. Vivamus euismod porta accumsan. Donec aliquam, tortor id elementum mollis, justo erat elementum diam, id ullamcorper quam nulla et nisl. Donec porttitor vehicula nisi ac fermentum. Suspendisse in lectus orci.
	</p>

	<p>
		Donec consequat suscipit augue a gravida. Phasellus vulputate semper molestie. Quisque nec varius massa. Vestibulum eros tortor, venenatis id venenatis vel, bibendum in justo. Nam sit amet justo id est molestie aliquet. Vivamus dapibus fringilla felis ac consequat. Mauris eu velit nisl, sed convallis enim. Curabitur tristique dolor vitae dui vestibulum et facilisis massa tincidunt. Aliquam in faucibus justo.
	</p>

	<p>
		Quisque arcu turpis, bibendum vel interdum at, viverra sit amet lectus. Sed nunc sem, ultrices non laoreet pharetra, semper at eros. Aliquam ut dui in ante pulvinar blandit et a eros. Phasellus gravida, lorem nec ultrices aliquam, nunc augue gravida quam, eu elementum enim magna a magna. Integer dictum tellus sed sapien consequat fermentum. Nam ac eros in nisl pulvinar consectetur ac at turpis. Maecenas sit amet turpis et odio vehicula sollicitudin. Vivamus erat eros, suscipit a ultrices sed, tristique ut dolor.
	</p>

	<p class="article-buttons">
		<a class="btn btn-small"><i class="icon-thumbs-up"></i> Show me more like this</a>
		<a class="btn btn-small"><i class="icon-thumbs-down"></i> Hide articles like these</a>
	</p>
</article>

<?php require 'footer.html.php' ?>
