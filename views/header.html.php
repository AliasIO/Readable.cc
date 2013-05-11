<!DOCTYPE html>

<html>
	<head>
		<meta charset="utf-8">

		<title><?php echo ( $this->name == 'index' ? '' : ( $this->get('pageTitle') . ' - ' ) ) . $this->htmlEncode($this->app->getConfig('siteName')) . ( $this->name == 'index' ? ' - ' . $this->get('pageTitle') : '' ) ?></title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<meta name="description" content="<?php echo $this->get('pageDescription') ? $this->get('pageDescription') . ' ' : '' ?>Readable.cc is an RSS reader that makes feeds readable. Vote on articles to improve your personal reading list.">
		<meta name="keywords"    content="readable, feed, rss, atom, reader, google, news, articles, content, reading">

		<?php if ( $this->get('canonicalUrl') ): ?>
		<link rel="canonical" href="<?php echo $this->get('canonicalUrl') ?>">
		<?php endif ?>

		<link href="/views/lib/bootstrap/css/readable.css" rel="stylesheet">
		<link href="/views/lib/entypo/entypo.css" rel="stylesheet">
		<link href="/views/css/layout.css" rel="stylesheet">

		<script>
			var readable = {};

			(function(app) {
				app.email      = '<?php echo str_replace('@', ' ', $this->app->getConfig('emailFrom')) ?>';
				app.controller = '<?php echo $this->get('controller') ?>';
				app.args       = '<?php echo implode('/', $this->app->getArgs()) ?>';
				app.sessionId  = '<?php echo $this->app->getSingleton('session')->getId() ?>';
				app.signedIn   = <?php echo $this->app->getSingleton('session')->get('id') ? 'true' : 'false' ?>;
				app.itemCount  = 0;
			}(readable));
		</script>
	</head>
	<body>
		<header class="navbar navbar-fixed-top">
			<nav class="navbar-inner">
				<div class="container">
					<h1 class="brand"><a href="/" title="Readable.cc RSS Reader"><i class="entypo home"></i><span> Readable.cc <strong>RSS Reader</strong></span></a></h1>

					<p>
						Readable.cc is the best web-based Google Reader alternative. RSS feeds are made readable and interesting content is identified algorithmically.
					</p>

					<ul class="nav pull-right">
						<?php if ( $this->app->getSingleton('session')->get('id') ): ?>
						<li class="reading <?php echo $this->name == 'reading' ? 'active' : '' ?>"><a href="/reading"><i class="entypo rss"    ></i><span> My Reading <span id="item-count">(<span>0</span>)</span></span></a></li>
						<li class="saved   <?php echo $this->name == 'saved'   ? 'active' : '' ?>"><a href="/saved"  ><i class="entypo install"></i><span> Saved</span></a></li>

						<li class="email dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="entypo tools"></i><span><?php echo $this->app->getSingleton('session')->get('email') ?> <i class="entypo chevron-down"></i></span></a>

							<ul class="dropdown-menu">
								<li class="account"      ><a href="/account"      ><i class="entypo user"  ></i><span> Account</span></a></li>
								<li class="subscriptions"><a href="/subscriptions"><i class="entypo rss"   ></i><span> Subscriptions</span></a></li>
								<li class="signout"      ><a href="/signout"      ><i class="entypo logout"></i><span> Sign out</span></a></li>
							</ul>
						</li>
						<?php else: ?>
						<li class="signup <?php echo $this->name == 'signup' ? 'active' : '' ?>"><a href="/signup"><i class="entypo add-user"></i><span> Create account</span></a></li>
						<li class="signin <?php echo $this->name == 'signin' ? 'active' : '' ?>"><a href="/signin"><i class="entypo login"   ></i><span> Sign in</span></a></li>
						<?php endif ?>
					</ul>
				</div>
			</nav><!-- /navbar-inner -->
		</header><!-- /navbar -->

		<div id="contents" class="container">
