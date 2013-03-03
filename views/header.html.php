<!DOCTYPE html>

<html>
	<head>
		<title><?php echo $this->htmlEncode($this->app->getConfig('siteName')) . ' - ' . $this->get('pageTitle') ?></title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link href="<?php echo $this->app->getRootPath() ?>views/lib/bootstrap/css/readable.min.css" rel="stylesheet">
		<link href="<?php echo $this->app->getRootPath() ?>views/css/layout.css" rel="stylesheet">
	</head>
	<body>
		<header class="navbar navbar-fixed-top ">
			<div class="navbar-inner">
				<div class="container">
					<a class="brand" href="<?php echo $this->app->getRootPath() ?>">Readable.cc</a>
					<nav class="nav-collapse">
						<ul class="nav">
							<li class="<?php echo $this->name == 'index' ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>"><i class="icon-fire"></i> Popular</a></li>
							<li class="<?php echo $this->name == 'personal' ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>personal"><i class="icon-star"></i> Personal</a></li>
						</ul>

						<ul class="nav pull-right">
							<?php if ( $this->app->getSingleton('session')->get('id') ): ?>
							<li class="<?php echo $this->name == 'feeds'   ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>feeds"><i class="icon-align-justify"></i> Manage feeds</a></li>
							<li class="<?php echo $this->name == 'signout' ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>signout"><i class="icon-off"></i> Sign out</a></li>
							<?php else: ?>
							<li class="<?php echo $this->name == 'signup'  ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>signup"><i class="icon-user"></i> Create account</a></li>
							<li class="<?php echo $this->name == 'signin'  ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>signin"><i class="icon-hand-right"></i> Sign in</a></li>
							<?php endif ?>
						</ul>
					</nav><!-- /.nav-collapse -->
				</div>
			</div><!-- /navbar-inner -->
		</header><!-- /navbar -->

		<div class="container">
