<!DOCTYPE html>

<html>
	<head>
		<meta charset="utf-8">

		<title><?php echo ( $this->name == 'index' ? '' : ( $this->get('pageTitle') . ' - ' ) ) . $this->htmlEncode($this->app->getConfig('siteName')) . ( $this->name == 'index' ? ' - RSS Reader' : '' ) ?></title>

		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

		<meta name="description" content="<?php echo $this->get('pageDescription') ? $this->get('pageDescription') . ' ' : '' ?>Readable.cc is an RSS reader that makes feeds readable. Vote on articles to improve your personal reading list.">
		<meta name="keywords"    content="readable, feed, rss, atom, reader, google, news, articles, content, reading">

		<link href="<?php echo $this->app->getRootPath() ?>views/lib/bootstrap/css/readable.css" rel="stylesheet">
		<link href="<?php echo $this->app->getRootPath() ?>views/lib/entypo/entypo.css" rel="stylesheet">
		<link href="<?php echo $this->app->getRootPath() ?>views/css/layout.css?e" rel="stylesheet">

		<script>
			var readable = {};

			(function(app) {
				app.email      = '<?php echo str_replace('@', ' ', $this->app->getConfig('emailFrom')) ?>';
				app.controller = '<?php echo $this->app->getControllerName() ?>';
				app.args       = '<?php echo implode('/', $this->app->getArgs()) ?>';
				app.sessionId  = '<?php echo $this->app->getSingleton('session')->getId() ?>';
				app.signedIn   = <?php echo $this->app->getSingleton('session')->get('id') ? 'true' : 'false' ?>;
				app.itemCount  = 0;
				app.page       = <?php echo !empty($_GET['page']) && (int) $_GET['page'] - 1 ? (int) $_GET['page'] - 1 : 0 ?>;
			}(readable));
		</script>

		<!--[if lt IE 9]>
		<script src="<?php echo $this->app->getRootPath() ?>views/lib/html5shiv.js"></script>
		<![endif]-->
	</head>
	<body>
		<header>
			<div class="container">
				<h1><a href="<?php echo $this->app->getRootPath() ?>" title="Readable.cc RSS Reader">Readable.cc <strong>RSS Reader</strong></a></h1>

				<p>
					Readable.cc is the best web-based Google Reader alternative. RSS feeds are made readable and interesting content is identified algorithmically.
				</p>

				<h2 class="active">
					<a href="javascript: void(0);">
						<?php echo $this->get('pageTitle') ?>
						<?php if ( $this->app->getControllerName() === 'Reading' || $this->app->getControllerName() === 'Folder' ): ?>
						<span class="item-count">(<span>0</span>)</span>
						<?php endif ?>
						<i class="entypo chevron-down"></i>
					</a>
				</h2>

				<ul class="collapsed">
					<?php if ( $this->app->getSingleton('session')->get('id') ): ?>
					<li class="reading <?php echo $this->name == 'reading' ? 'active' : '' ?>">
						<a href="<?php echo $this->app->getRootPath() ?>reading">My Reading<span class="item-count"> (<span>0</span>)</span>
					</a></li>

					<?php if ( $folders = $this->app->getSingleton('helper')->getUserFolders() ): ?>
					<li class="folders <?php echo $this->app->getControllerName() === 'Folder' ? 'active' : '' ?>">
						<a href="javascript: void(0);">Folder</i><span class="item-count"> (<span>0</span>)</span></a>

						<ul class="collapsed">
							<?php foreach ( $folders as $folder ): ?>
							<li class="folder"><a href="<?php echo $this->app->getSingleton('helper')->getFolderLink($folder->id, $folder->title) ?>">
								<?php echo $this->htmlEncode($folder->title) ?>
							</a></li>
							<?php endforeach ?>
						</ul>
					</li>
					<?php endif ?>

					<li class="saved   <?php echo $this->name == 'saved'   ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>saved"  >Saved</a></li>
					<li class="help    <?php echo $this->name == 'help'    ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>help"   >Help</a></li>

					<li class="email">
						<a href="javascript: void(0);"><span><?php echo $this->app->getSingleton('session')->get('email') ?></span>&nbsp;<i class="entypo chevron-down"></i></i></a>

						<ul class="collapsed">
							<?php if ( $this->app->getControllerName() === 'Reading' || $this->app->getControllerName() === 'Folder' ): ?>
							<li class="mark-all-read"><a href="javascript: void(0);">Mark all read</a></li>
							<?php endif ?>
							<li class="account       <?php echo $this->name == 'account'       ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>account"      >Account</a></li>
							<li class="subscriptions <?php echo $this->name == 'subscriptions' ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>subscriptions">Subscriptions</a></li>
							<li class="signout       <?php echo $this->name == 'signout'       ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>signout"      >Sign out</a></li>
						</ul>
					</li>
					<?php else: ?>
					<li class="help   <?php echo $this->name == 'help'   ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>help"  >Help</a></li>
					<li class="signup <?php echo $this->name == 'signup' ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>signup" title="Sign up for free!"><span>Create account</span></a></li>
					<li class="signin <?php echo $this->name == 'signin' ? 'active' : '' ?>"><a href="<?php echo $this->app->getRootPath() ?>signin">Sign in</a></li>
					<?php endif ?>
				</ul>
			</div>
		</header>

		<div id="contents" class="container">
