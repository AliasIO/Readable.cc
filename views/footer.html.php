			<footer>
				<p>
					<a href="/about">About</a> &nbsp;-&nbsp;
					<a href="/contact">Contact</a>
				</p>
			</footer>

			<div class="modal modal-signin">
				<h4>Not signed in</h4>

				<p>You're missing out. Please <a href="/signin">sign in</a> or <a href="/signup">create an account</a>.</p>
			</div>
		</div> <!-- /container -->

		<p id="feedback">
			<a href="/contact">Feedback</a>
		</p>

		<script src="/views/lib/jquery-1.9.1.min.js"></script>
		<script src="/views/lib/bootstrap/js/bootstrap.min.js"></script>
		<script src="/views/lib/mousetrap.min.js"></script>
		<script src="/views/js/readable.js"></script>

		<script>
			readable.email      = '<?php echo str_replace('@', ' ', $this->app->getConfig('emailFrom')) ?>';
			readable.controller = '<?php echo $this->get('controller') ?>';
			readable.args       = '<?php echo implode('/', $this->app->getArgs()) ?>';
			readable.sessionId  = '<?php echo $this->app->getSingleton('session')->getId() ?>';
			readable.signedIn   = <?php echo $this->app->getSingleton('session')->get('id') ? 'true' : 'false' ?>;
		</script>

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
