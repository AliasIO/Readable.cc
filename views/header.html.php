<!DOCTYPE html>

<html>
	<head>
		<meta charset="utf-8">

		<title><?php echo $this->get('pageTitle') . ' - ' . $this->htmlEncode($this->app->getConfig('siteName')) ?></title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<meta name="description" content="<?php echo $this->get('pageDescription') ? $this->get('pageDescription') : 'The readable feed reader. Promotes interesting articles to the top of your personalised reading list.' ?>">
		<meta name="keywords"    content="readable, feed, rss, atom, reader, google, news, articles, content, reading">

		<link href="/views/lib/bootstrap/css/readable.css" rel="stylesheet">
		<link href="/views/lib/entypo/entypo.css" rel="stylesheet">
		<link href="/views/css/layout.css" rel="stylesheet">
	</head>
	<body>
		<header class="navbar navbar-fixed-top">
			<nav class="navbar-inner">
				<div class="container">
					<a class="brand" href="/"><i class="entypo home"></i><span> Readable.cc</span></a>

					<ul class="nav pull-right">
						<?php if ( $this->app->getSingleton('session')->get('id') ): ?>
						<li class="reading <?php echo $this->name == 'reading' ? 'active' : '' ?>"><a href="/reading"><i class="entypo rss"    ></i><span> My Reading</span></a></li>
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
