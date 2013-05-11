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

			<div class="modal modal-mark-all-read">
				<h4>Mark all articles &lsquo;read&rsquo;</h4>

				<p>
					Are you sure you wish to clear your reading list?
				</p>

				<p>
					<button id="mark-all-read" class="btn btn-small btn-danger">Clear reading list</button>
					<button class="btn btn-small alert-cancel">Continue reading</button>
				</p>
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
