<!DOCTYPE html>

<html>
	<head>
		<meta charset="utf-8">

		<title><?php echo $this->htmlEncode($this->app->getConfig('siteName')) . ' - ' . $this->get('pageTitle') ?></title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<meta name="description" content="The readable feed reader. Content automagically filtered to suit your interest.">
		<meta name="keywords"    content="readable, feed, rss, atom, reader, google, news, articles, content, reading">

		<link href="/views/lib/bootstrap/css/readable.css" rel="stylesheet">
		<link href="/views/lib/entypo/entypo.css" rel="stylesheet">
		<link href="/views/css/layout.css" rel="stylesheet">

		<script src="/views/lib/jquery-1.9.1.min.js"></script>
		<script src="/views/lib/mousetrap.min.js"></script>
		<script src="/views/js/readable.js"></script>

		<script>
			readable.view      = '<?php echo $this->name ?>';
			readable.sessionId = '<?php echo $this->app->getSingleton('session')->getId() ?>';
			readable.signedIn  = <?php echo $this->app->getSingleton('session')->get('id') ? 'true' : 'false' ?>;
		</script>
	</head>
	<body>
		<header class="navbar navbar-fixed-top">
			<nav class="navbar-inner">
				<div class="container">
					<a class="brand" href="/">Readable.cc</a>

					<ul class="nav pull-right">
						<?php if ( $this->app->getSingleton('session')->get('id') ): ?>
						<li class="<?php echo $this->name == 'personal' ? 'active' : '' ?>"><a href="/reading"><i class="entypo rss"></i><span> My Reading</span></a></li>
						<li class="<?php echo $this->name == 'saved'    ? 'active' : '' ?>"><a href="/saved"><i class="entypo install"></i><span> Saved</span></a></li>

						<li class="dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo $this->app->getSingleton('session')->get('email') ?> <i class="entypo chevron-down"></i></a>

							<ul class="dropdown-menu">
								<li><a href="/account"><i class="entypo user"></i><span> Account</span></a></li>
								<li><a href="/subscriptions"><i class="entypo rss"></i><span> Subscriptions</span></a></li>
								<li><a href="/signout"><i class="entypo logout"></i><span> Sign out</span></a></li>
							</ul>
						</li>
						<?php else: ?>
						<li class="<?php echo $this->name == 'signup'       ? 'active' : '' ?>"><a href="/signup"><i class="entypo add-user"></i><span> Create account</span></a></li>
						<li class="<?php echo $this->name == 'signin'       ? 'active' : '' ?>"><a href="/signin"><i class="entypo login"></i><span> Sign in</span></a></li>
						<?php endif ?>
					</ul>
				</div>
			</nav><!-- /navbar-inner -->
		</header><!-- /navbar -->

		<div id="contents" class="container">
