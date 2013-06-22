			<div class="modal modal-signin">
				<h4>Not signed in</h4>

				<p>You're missing out. Please <a href="<?= $this->app->getRootPath() ?>signin">sign in</a> or <a href="<?= $this->app->getRootPath() ?>signup">create an account</a>.</p>
			</div>

			<div class="modal modal-mark-all-read">
				<h4>Mark all articles &lsquo;read&rsquo;</h4>

				<p>
					Are you sure you wish to clear this list?
				</p>

				<p>
					<button class="btn btn-primary mark-all-read-confirm">Clear list</button>
					<button class="btn alert-cancel">Continue reading</button>
				</p>
			</div>

			<div class="modal modal-no-more-items">
				<h4>That's all folks</h4>

				<p>
					You're at the last article.
				</p>
			</div>
		</div> <!-- /container -->

		<div id="overlay"></div>

		<p id="feedback">
			<a href="<?= $this->app->getRootPath() ?>help#contact">Feedback</a>
		</p>

		<!--
		<script src="<?= $this->app->getRootPath() ?>lib/mathjax/MathJax.js?config=readable.js"></script>
		-->

		<script src="<?= $this->app->getRootPath() ?>lib/jquery-1.9.1.min.js"></script>
		<script src="<?= $this->app->getRootPath() ?>js/readable.js?n"></script>

		<script>
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-216336-24']);
			_gaq.push(['_setDomainName', 'readable.cc']);
			_gaq.push(['_setAllowLinker', true]);
			_gaq.push(['_trackPageview']);

			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>

		<!-- Piwik -->
		<script type="text/javascript">
			var _paq = _paq || [];
			_paq.push(['trackPageView']);
			_paq.push(['enableLinkTracking']);
			(function() {
				var u=(("https:" == document.location.protocol) ? "https" : "http") + "://piwik.alias.io//";
				_paq.push(['setTrackerUrl', u+'piwik.php']);
				_paq.push(['setSiteId', 1]);
				var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript';
				g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
			})();

		</script>
		<noscript><p><img src="http://piwik.alias.io/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
		<!-- End Piwik Code -->
	</body>
</html>
