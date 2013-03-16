			<div class="modal modal-signin">
				<h4>Not signed in</h4>

				<p>You're missing out. Please <a href="<?php echo $this->app->getRootPath() ?>signin">sign in</a> or <a href="<?php echo $this->app->getRootPath() ?>signup">create an account</a>.</p>
			</div>

			<div class="modal modal-welcome">
				<h4>Hello</h4>

				<p>
					Readable.cc is a news reader that learns to recognise articles that interests you.
				</p>

				<p>
					<a href="<?php echo $this->app->getRootPath() ?>signup">Create an account</a> to manage your own subscriptions (RSS and Atom feeds.)
				</p>

				<p>
					Navigate to next and previous articles with <code>j</code> and <code>k</code>.
				</p>

				<p>
					<small>Click this window to dismiss.</small>
				</p>
			</div>
		</div> <!-- /container -->

		<p id="feedback">
			<a href="https://github.com/ElbertF/readable.cc/issues">Feedback</a>
		</p>

		<script src="<?php echo $this->app->getRootPath() ?>views/lib/bootstrap/js/bootstrap.min.js"></script>

		<script type="text/javascript">
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
