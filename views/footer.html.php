			<footer>
				<p>
					<a href="<?php echo $this->app->getRootPath() ?>about">About</a> &nbsp;-&nbsp;
					<a href="<?php echo $this->app->getRootPath() ?>contact">Contact</a>
				</p>
			</footer>

			<div class="modal modal-signin">
				<h4>Not signed in</h4>

				<p>You're missing out. Please <a href="<?php echo $this->app->getRootPath() ?>signin">sign in</a> or <a href="<?php echo $this->app->getRootPath() ?>signup">create an account</a>.</p>
			</div>

			<div class="modal modal-mark-all-read">
				<h4>Mark all articles &lsquo;read&rsquo;</h4>

				<p>
					Are you sure you wish to clear your reading list?
				</p>

				<p>
					<button class="btn btn-small btn-primary rmark-all-read-confirm">Clear reading list</button>
					<button class="btn btn-small alert-cancel">Continue reading</button>
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
			<a href="<?php echo $this->app->getRootPath() ?>contact">Feedback</a>
		</p>

		<script src="<?php echo $this->app->getRootPath() ?>views/lib/jquery-1.9.1.min.js"></script>
		<script src="<?php echo $this->app->getRootPath() ?>views/lib/mousetrap.min.js"></script>
		<script src="<?php echo $this->app->getRootPath() ?>views/js/readable.js"></script>

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
	</body>
</html>
