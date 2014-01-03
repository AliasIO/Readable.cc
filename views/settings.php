<?php require 'header.php' ?>

<h1><?= $this->get('pageTitle') ?></h1>

<?php if ( $this->get('success') ): ?>
<div class="alert alert-success">
	<?= $this->get('success'); ?>
</div>
<?php endif ?>

<?php if ( $this->get('error') ): ?>
<div class="alert alert-error">
	<?= $this->get('error', false); ?>
</div>
<?php endif ?>

<?php /*
<div class="jump">
	<p>
		Jump to:
	</p>

	<ul>
		<li><a href="#payments">Payments</a></li>
		<li><a href="#account">Account</a></il>
	</ul>
</div>

<?php $payments = $this->get('payments') ?>
*/ ?>

<form id="form-settings" method="post" action="<?= $this->app->getRootPath() ?>settings" class="well">
	<input type="hidden" name="form" value="settings">
	<input type="hidden" name="sessionId" value="<?= $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group <?= $this->get('error-links') ? 'error' : '' ?>">
			<label class="control-label" for="links">External links</label>

			<div class="controls">
				<select name="links" id="links">
					<option value="0"<?= $this->get('links') == 0 ? ' selected="selected"' : '' ?>>Open in current tab</option>
					<option value="1"<?= $this->get('links') == 1 ? ' selected="selected"' : '' ?>>Open in new tab</option>
				</select>
			</div>
		</div>

		<div class="control-group <?= $this->get('error-order') ? 'error' : '' ?>">
			<label class="control-label" for="order">Sort articles by</label>

			<div class="controls">
				<select name="order" id="order">
					<option value="0"<?= $this->get('order') == 0 ? ' selected="selected"' : '' ?>>Time and relevance</option>
					<option value="1"<?= $this->get('order') == 1 ? ' selected="selected"' : '' ?>>Time</option>
				</select>
			</div>
		</div>

		<div class="control-group <?= $this->get('error-password-repeat') ? 'error' : '' ?>">
			<label class="control-label" for="timezone">Time zone</label>

			<div class="controls">
				<select name="timezone" id="timezone" class="input-block-level">
					<?php foreach ( $this->get('timeZones') as $offset => $timeZone ): ?>
					<option value="<?= $offset ?>" <?= $this->get('timezone') == $offset ? 'selected="selected"' : '' ?>><?= $timeZone ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit">Save changes</button>
			</div>
		</div>
	</fieldset>
</form>

<div class="divider"></div>

<h2 id="account">Account</h2>

<form id="form-settings-account" method="post" action="<?= $this->app->getRootPath() ?>settings" class="well">
	<input type="hidden" name="form" value="account">
	<input type="hidden" name="sessionId" value="<?= $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group <?= $this->get('error-email') ? 'error' : '' ?>">
			<label class="control-label" for="email">Email address</label>

			<div class="controls">
				<input id="email" name="email" class="input-xlarge" type="email" value="<?= $this->get('email') ?>">
			</div>
		</div>

		<div class="control-group <?= $this->get('error-password') ? 'error' : '' ?>">
			<label class="control-label" for="password">New password</label>

			<div class="controls">
				<input id="password" name="password" class="input-xlarge" type="password" autocomplete="off">
			</div>
		</div>

		<div class="control-group <?= $this->get('error-password-repeat') ? 'error' : '' ?>">
			<label class="control-label" for="password-repeat">Repeat password</label>

			<div class="controls">
				<input id="password-repeat" name="password-repeat" class="input-xlarge" type="password" autocomplete="off">
			</div>
		</div>
	</fieldset>

	<fieldset>
		<div class="control-group <?= $this->get('error-current-password') ? 'error' : '' ?>">
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

<?php /*
<div class="divider"></div>

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
				<?= $payment->currency . ' ' . number_format($payment->amount / 100, 2) ?>
			</td>
			<td>
				<?= $payment->created_at ?>
			</td>
			<td>
				<?= $payment->expires_at ?>
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
<?php require('views/pay-partial.php') ?>
<?php endif ?>
*/ ?>

<div class="divider"></div>

<h2 id="delete">Delete account</h2>

<p>
	Delete your account forever. Every record associated with your account will be deleted and unrecoverable.
	You can <a href="<?= $this->app->getRootPath() ?>subscriptions/export">export your subscriptions</a>.
</p>

<form id="form-settings-delete" method="post" action="<?= $this->app->getRootPath() ?>settings" class="well">
	<input type="hidden" name="form" value="delete">
	<input type="hidden" name="sessionId" value="<?= $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group <?= $this->get('error-password-delete') ? 'error' : '' ?>">
			<label class="control-label" for="password-delete">Password</label>

			<div class="controls">
				<input id="password-delete" name="password-delete" class="input-xlarge" type="password" autocomplete="off">
			</div>
		</div>

		<div class="control-group <?= $this->get('error-email') ? 'error' : '' ?>">
			<div class="controls">
				<button class="btn btn-danger" type="submit">Delete account</button>
			</div>
		</div>
	</fieldset>
</form>
<?php require 'footer.php' ?>
