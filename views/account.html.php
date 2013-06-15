<?php require 'header.html.php' ?>

<h1><?php echo $this->get('pageTitle') ?></h1>

<?php if ( $this->get('success') ): ?>
<div class="alert alert-success">
	<?php echo $this->get('success'); ?>
</div>
<?php endif ?>

<?php if ( $this->get('error') ): ?>
<div class="alert alert-error">
	<?php echo $this->get('error', false); ?>
</div>
<?php endif ?>

<div class="jump">
	<p>
		Jump to:
	</p>

	<ul>
		<li><a href="#payments">Payments</a></li>
		<li><a href="#delete">Delete</a></il>
	</ul>
</div>

<?php $payments = $this->get('payments') ?>

<form id="form-account" method="post" action="<?php echo $this->app->getRootPath() ?>account" class="well">
	<input type="hidden" name="sessionId" value="<?php echo $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group <?php echo $this->get('error-email') ? 'error' : '' ?>">
			<label class="control-label" for="email">Email address</label>

			<div class="controls">
				<input id="email" name="email" class="input-xlarge" type="email" value="<?php echo $this->get('email') ?>">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-password') ? 'error' : '' ?>">
			<label class="control-label" for="password">New password</label>

			<div class="controls">
				<input id="password" name="password" class="input-xlarge" type="password" autocomplete="off">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-password-repeat') ? 'error' : '' ?>">
			<label class="control-label" for="password-repeat">Repeat password</label>

			<div class="controls">
				<input id="password-repeat" name="password-repeat" class="input-xlarge" type="password" autocomplete="off">
			</div>
		</div>
	</fieldset>

	<fieldset>
		<div class="control-group <?php echo $this->get('error-password-repeat') ? 'error' : '' ?>">
			<label class="control-label" for="timezone">Time zone</label>

			<div class="controls">
				<select name="timezone" id="timezone" class="input-block-level">
					<?php foreach ( $this->get('timeZones') as $offset => $timeZone ): ?>
					<option value="<?php echo $offset ?>" <?php echo $this->get('timezone') == $offset ? 'selected="selected"' : '' ?>><?php echo $timeZone ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<div class="control-group <?php echo $this->get('error-current-password') ? 'error' : '' ?>">
			<label class="control-label" for="current-password">Current password</label>

			<div class="controls">
				<input id="current-password" name="current-password" class="input-xlarge" type="password" autocomplete="off">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit">Update account</button>
			</div>
		</div>
	</fieldset>
</form>

<div class="divider"></div>

<!--
<h2 id="payments">Payments</h2>

<?php if ( $payments ): ?>
<?php if ( $this->get('paid') ): ?>
<p>
	Thank you for your support!
</p>
<?php endif ?>

<table id="table-payments" class="table table-list">
	<thead>
		<tr>
			<th>Amount</th>
			<th>Paid at</th>
			<th>Valid until</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $payments as $payment ): ?>
		<tr>
			<td>
				<?php echo $payment->currency . ' ' . number_format($payment->amount / 100, 2) ?>
			</td>
			<td>
				<?php echo $payment->created_at ?>
			</td>
			<td>
				<?php echo $payment->expires_at ?>
			</td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
<?php else: ?>
<p>
	<em>You have not made any payments.</em>
</p>
<?php endif ?>

<?php if ( !$this->get('paid') ): ?>
<?php require('views/pay-partial.html.php') ?>
<?php endif ?>

<div class="divider"></div>
-->

<h2 id="delete">Delete</h2>

<p>
	Delete your account forever. Every record associated with your account will be deleted and unrecoverable.
	You can <a href="<?php echo $this->app->getRootPath() ?>subscriptions/export">export your subscriptions</a>.
</p>

<form id="form-account-delete" method="post" action="<?php echo $this->app->getRootPath() ?>account/delete" class="well">
	<input type="hidden" name="sessionId" value="<?php echo $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group <?php echo $this->get('error-password-delete') ? 'error' : '' ?>">
			<label class="control-label" for="password-delete">Password</label>

			<div class="controls">
				<input id="password-delete" name="password-delete" class="input-xlarge" type="password" autocomplete="off">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-email') ? 'error' : '' ?>">
			<div class="controls">
				<button class="btn btn-danger" type="submit">Delete account</button>
			</div>
		</div>
	</fieldset>
</form>
<?php require 'footer.html.php' ?>
