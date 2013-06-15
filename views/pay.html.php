<?php require 'header.html.php' ?>

<h1><?php echo $this->get('pageTitle') ?></h1>

<?php if ( $this->get('success') ): ?>
<div class="alert alert-success">
	<?php echo $this->get('success', false); ?>
</div>
<?php endif ?>

<?php if ( $this->get('error') ): ?>
<div class="alert alert-error">
	<?php echo $this->get('error', false); ?>
</div>
<?php endif ?>

<p>
	Almost done. Please provide your payment details and billing address.
</p>

<p>
	<em>Your money will go towards hosting costs and ongoing maintenance of the site. Occasionally it may go towards beer.</em>
</p>

<form id="form-pay" method="post" action="<?php echo $this->app->getRootPath() ?>pay" class="well">
	<input type="hidden" name="form" value="pay">
	<input type="hidden" name="sessionId" value="<?php echo $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group <?php echo $this->get('error-url') ? 'error' : '' ?>">
			<label class="control-label" for="amount">Amount</label>

			<div class="controls">
				<select id="amount" name="amount">
					<option>Select amount&hellip;</option>
					<option value="2"<?php   echo $this->get('amount') == 2   ? ' selected="selected"' : '' ?>>$2 / month</option>
					<option value="5"<?php   echo $this->get('amount') == 5   ? ' selected="selected"' : '' ?>>$5 / month</option>
					<option value="10"<?php  echo $this->get('amount') == 10  ? ' selected="selected"' : '' ?>>$10 / month</option>
					<option value="50"<?php  echo $this->get('amount') == 50  ? ' selected="selected"' : '' ?>>$50 / month</option>
					<option value="100"<?php echo $this->get('amount') == 100 ? ' selected="selected"' : '' ?>>$100 / month</option>
				</select>

				<select id="currency" name="currency">
					<option<?php echo $this->get('currency') == 'AUD' ? ' selected="selected"' : '' ?>>AUD</option>
					<option<?php echo $this->get('currency') == 'USD' ? ' selected="selected"' : '' ?>>USD</option>
				</select>
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-months') ? 'error' : '' ?>">
			<label class="control-label" for="months">Pay for</label>

			<div class="controls">
				<select id="months" name="months">
					<option value="1"<?php  echo $this->get('months') == 1  ? ' selected="selected"' : '' ?>>One month (non-recurring)</option>
					<option value="6"<?php  echo $this->get('months') == 6  ? ' selected="selected"' : '' ?>>Six months (non-recurring)</option>
					<option value="12"<?php echo $this->get('months') == 12 ? ' selected="selected"' : '' ?>>One year (non-recurring)</option>
				</select>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="months">Total amount</label>

			<div id="total-amount" class="controls">
				$0
			</div>
		</div>
	</fieldset>
	<fieldset>
		<div class="control-group <?php echo $this->get('error-name') ? 'error' : '' ?>">
			<label class="control-label" for="name">Name on card</label>

			<div class="controls">
				<input id="name" name="name" type="text" placeholder="Bruce Wayne" value="<?php echo $this->get('name') ?>">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-number') ? 'error' : '' ?>">
			<label class="control-label" for="number">Card number</label>

			<div class="controls">
				<input id="number" name="number" type="text" placeholder="1234 1234 1234 1234" value="<?php echo $this->get('number') ?>"> <img src="views/images/cc.png" width="88" height="27" alt="">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-expiry') ? 'error' : '' ?>">
			<label class="control-label" for="expiry-month">Expiry</label>

			<div class="controls">
				<select id="expiry-month" name="expiry-month">
					<option value="">MM</option>
					<?php for ( $i = 1; $i <= 12; $i ++ ): ?>
					<option value="<?php echo $i ?>"<?php echo $this->get('expiry-month') == $i ? ' selected="selected"' : '' ?>><?php printf('%02d', $i) ?></option>
					<?php endfor ?>
				</select>

				<select id="expiry-year" name="expiry-year">
					<option value="">YYYY</option>
					<?php for ( $i = date('Y'); $i <= date('Y') + 10; $i ++ ): ?>
					<option value="<?php echo $i ?>"<?php echo $this->get('expiry-year') == $i ? ' selected="selected"' : '' ?>><?php echo $i ?></option>
					<?php endfor ?>
				</select>
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-cvc') ? 'error' : '' ?>">
			<label class="control-label" for="cvc">Security code (CVC)</label>

			<div class="controls">
				<input id="cvc" name="cvc" type="text" placeholder="123" value="<?php echo $this->get('cvc') ?>">
			</div>
		</div>
	</fieldset>
	<fieldset>
		<div class="control-group <?php echo $this->get('error-address') ? 'error' : '' ?>">
			<label class="control-label" for="name">Address</label>

			<div class="controls">
				<input id="address-line-1" name="address-line-1" type="text" placeholder="1007 Mountain Drive" value="<?php echo $this->get('address-line-1') ?>"><br>
				<input id="address-line-2" name="address-line-2" type="text" placeholder=""                    value="<?php echo $this->get('address-line-2') ?>">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-address-city') ? 'error' : '' ?>">
			<label class="control-label" for="name">City</label>

			<div class="controls">
				<input id="address-city" name="address-city" type="text" placeholder="Gotham City" value="<?php echo $this->get('address-city') ?>">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-address-postcode') ? 'error' : '' ?>">
			<label class="control-label" for="address-postcode">Postcode</label>

			<div class="controls">
				<input id="address-postcode" name="address-postcode" type="text" placeholder="12345" value="<?php echo $this->get('address-postcode') ?>">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-address-state') ? 'error' : '' ?>">
			<label class="control-label" for="address-state">State</label>

			<div class="controls">
				<input id="address-state" name="address-state" type="text" placeholder="" value="<?php echo $this->get('address-state') ?>">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-address-country') ? 'error' : '' ?>">
			<label class="control-label" for="address-country">Country</label>

			<div class="controls">
				<select id="address-country" name="address-country">
					<?php foreach ( $this->get('countries') as $code => $country ): ?>
					<option value="<?php echo $code ?>"<?php echo $this->get('address-country') == $code ? ' selected="selected"' : '' ?>><?php echo $country ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit">Pay</button><div class="loading"></div><span class="message"></span>
			</div>
		</div>
	</fieldset>
</form>

<?php require 'footer.html.php' ?>
