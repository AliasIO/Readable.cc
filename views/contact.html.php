<?php require 'header.html.php' ?>

<div class="page-header">
	<h1><?php echo $this->get('pageTitle') ?></h1>
</div>

<p>
	If you have any suggestions, questions or just want to say hi, please send an email to:
</p>

<p>
	<a class="contact-email" href="mailto:<?php echo $this->app->getConfig('emailHoneyPot') ?>"><?php echo $this->app->getConfig('emailHoneyPot') ?></a>
</p>

<p>
	<br>Follow Readable.cc on Twitter:
</p>

<p>
	<a href="https://twitter.com/<?php echo $this->app->getConfig('twitterHandle') ?>">@<?php echo $this->app->getConfig('twitterHandle') ?></a>
</p>

<?php require 'footer.html.php' ?>
