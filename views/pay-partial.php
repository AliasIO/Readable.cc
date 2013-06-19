<p>
	You are free to evaluate <?= $this->app->getConfig('siteName') ?> for as long as you wish.
	If you feel compelled to give your support, consider making a one time payment of however much it's worth to you.
</p>

<form id="form-pay" method="post" action="<?= $this->app->getRootPath() ?>pay" class="well">
	<input type="hidden" name="form" value="pay-partial">
	<input type="hidden" name="sessionId" value="<?= $this->app->getSingleton('session')->getId() ?>">

	<fieldset>
		<div class="control-group <?= $this->get('error-url') ? 'error' : '' ?>">
			<label class="control-label" for="amount">Amount</label>

			<div class="controls">
				<select id="amount" name="amount">
					<option>Select amount&hellip;</option>
					<option value="2">$2 / month</option>
					<option value="5" selected="selected">$5 / month</option>
					<option value="10">$10 / month</option>
					<option value="50">$50 / month</option>
					<option value="100">$100 / month</option>
				</select>

				<select id="currency" name="currency">
					<option>AUD</option>
					<option selected="selected">USD</option>
				</select>
			</div>
		</div>

		<div class="control-group <?= $this->get('error-months') ? 'error' : '' ?>">
			<label class="control-label" for="months">Pay for</label>

			<div class="controls">
				<select id="months" name="months">
					<option value="1">One month (non-recurring)</option>
					<option value="6">Six months (non-recurring)</option>
					<option value="12" selected="selected">One year (non-recurring)</option>
				</select>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit">Continue</button> &nbsp; <img src="images/cc.png" width="88" height="27" alt="">
			</div>
		</div>
	</fieldset>
</form>
