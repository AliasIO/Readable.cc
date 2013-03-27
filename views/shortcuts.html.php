<?php require 'header.html.php' ?>

<div class="page-header">
	<h1><?php echo $this->get('pageTitle') ?></h1>
</div>

<p>
	Keyboard shortcuts to help you move around on the reading lists (&lsquo;<a href="/">Popular Reading</a>&rsquo;, &lsquo;<a href="/reading">My Reading</a>&rsquo; and &lsquo;<a href="/saved">Saved</a>&rsquo;).
</p>

<table class="table table-bordered table-striped table-hover">
	<thead>
		<tr>
			<th>Key</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><code>j</code>, <code>s</code>, <code>Spacebar</code></td>
			<td>Next article</td>
		</tr>
		<tr>
			<td><code>k</code>, <code>w</code></td>
			<td>Previous article</td>
		</tr>
		<tr>
			<td><code>o</code>, <code>Enter</code></td>
			<td>Expand article (open)</td>
		</tr>
	</tbody>
</table>

<?php require 'footer.html.php' ?>