<!DOCTYPE html>

<html>
	<head>
		<meta charset="utf-8">

		<title><?php echo $this->htmlEncode($this->app->getConfig('siteName')) . ' - ' . $this->get('pageTitle') ?></title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<meta name="description" content="The readable feed reader. Content automagically filtered to suit your interest.">
		<meta name="keywords"    content="readable, feed, rss, atom, reader, google, news, articles, content, reading">

		<link href="<?php echo $this->app->getRootPath() ?>views/lib/bootstrap/css/readable.css" rel="stylesheet">
		<link href="<?php echo $this->app->getRootPath() ?>views/css/layout.css" rel="stylesheet">

		<script src="<?php echo $this->app->getRootPath() ?>views/lib/jquery-1.9.1.min.js"></script>
		<script src="<?php echo $this->app->getRootPath() ?>views/lib/mousetrap.min.js"></script>
		<script src="<?php echo $this->app->getRootPath() ?>views/js/readable.js"></script>

		<script>
			readable.rootPath  = '<?php echo $this->app->getRootPath() ?>';
			readable.view      = '<?php echo $this->name ?>';
			readable.sessionId = '<?php echo $this->app->getSingleton('session')->getId() ?>';
			readable.signedIn  = <?php echo $this->app->getSingleton('session')->get('id') ? 'true' : 'false' ?>;
		</script>
	</head>
	<body>
		<header class="navbar navbar-fixed-top">
			<nav class="navbar-inner">
				<div class="container">
					<a class="brand" href="<?php echo $this->app->getRootPath() ?>">Readable.cc</a>

					<ul class="nav">
						<li class="<?php echo $this->name == 'index'    ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>"><i class="icon-fire"></i><span> Popular</span></a></li>
						<li class="<?php echo $this->name == 'personal' ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>personal"><i class="icon-star"></i><span> Personal</span></a></li>
					</ul>

					<ul class="nav pull-right">
						<?php if ( $this->app->getSingleton('session')->get('id') ): ?>
						<li class="<?php echo $this->name == 'subsriptions' ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>subscriptions"><i class="icon-align-justify"></i><span> Subscriptions</span></a></li>
						<li class="<?php echo $this->name == 'account'      ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>account"><i class="icon-user"></i><span> Account</span></a></li>
						<li class="<?php echo $this->name == 'signout'      ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>signout"><i class="icon-off"></i><span> Sign out</span></a></li>
						<?php else: ?>
						<li class="<?php echo $this->name == 'signup'       ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>signup"><i class="icon-user"></i><span> Create account</span></a></li>
						<li class="<?php echo $this->name == 'signin'       ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>signin"><i class="icon-hand-right"></i><span> Sign in</span></a></li>
						<?php endif ?>
					</ul>
				</div>
			</nav><!-- /navbar-inner -->
		</header><!-- /navbar -->

		<div id="contents" class="container">
