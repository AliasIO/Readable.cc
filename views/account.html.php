<?php require 'header.html.php' ?>

<div class="page-header">
	<h1><?php echo $this->get('pageTitle') ?></h1>
</div>

<?php if ( $this->get('success') ): ?>
<div class="alert alert-success">
	<?php echo $this->get('success'); ?>
</div>
<?php endif ?>

<?php if ( $this->get('error') ): ?>
<div class="alert alert-error">
	<?php echo $this->get('error'); ?>
</div>
<?php endif ?>

<form method="post" action="<?php echo $this->app->getRootPath() ?>signup" class="form-signin form-horizontal well">
	<fieldset>
		<div class="control-group <?php echo $this->get('error-email') ? 'error' : '' ?>">
			<label class="control-label" for="email">Email address</label>

			<div class="controls">
				<input id="email" name="email" class="input-xlarge" type="email" value="<?php echo $this->get('email') ?>">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-new-password') ? 'error' : '' ?>">
			<label class="control-label" for="password">New password</label>

			<div class="controls">
				<input id="new-password" name="new-password" class="input-xlarge" type="password">
			</div>
		</div>

		<div class="control-group <?php echo $this->get('error-password-repeat') ? 'error' : '' ?>">
			<label class="control-label" for="password-repeat">Repeat password</label>

			<div class="controls">
				<input id="password" name="password-repeat" class="input-xlarge" type="password">
			</div>
		</div>
	</fieldset>

	<fieldset>
		<div class="control-group <?php echo $this->get('error-password-repeat') ? 'error' : '' ?>">
			<label class="control-label" for="timezone">Time zone</label>

			<div class="controls">
				<select name="timezone" id="timezone" class="input-block-level">
					<option value="-12.0">(GMT -12:00) Eniwetok, Kwajalein</option>
					<option value="-11.0">(GMT -11:00) Midway Island, Samoa</option>
					<option value="-10.0">(GMT -10:00) Hawaii</option>
					<option value="-9.0">(GMT -9:00) Alaska</option>
					<option value="-8.0">(GMT -8:00) Pacific Time (US &amp; Canada)</option>
					<option value="-7.0">(GMT -7:00) Mountain Time (US &amp; Canada)</option>
					<option value="-6.0">(GMT -6:00) Central Time (US &amp; Canada), Mexico City</option>
					<option value="-5.0">(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima</option>
					<option value="-4.0">(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>
					<option value="-3.5">(GMT -3:30) Newfoundland</option>
					<option value="-3.0">(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>
					<option value="-2.0">(GMT -2:00) Mid-Atlantic</option>
					<option value="-1.0">(GMT -1:00 hour) Azores, Cape Verde Islands</option>
					<option value="0.0">(GMT) Western Europe Time, London, Lisbon, Casablanca</option>
					<option value="1.0">(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris</option>
					<option value="2.0">(GMT +2:00) Kaliningrad, South Africa</option>
					<option value="3.0">(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>
					<option value="3.5">(GMT +3:30) Tehran</option>
					<option value="4.0">(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
					<option value="4.5">(GMT +4:30) Kabul</option>
					<option value="5.0">(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>
					<option value="5.5">(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>
					<option value="5.75">(GMT +5:45) Kathmandu</option>
					<option value="6.0">(GMT +6:00) Almaty, Dhaka, Colombo</option>
					<option value="7.0">(GMT +7:00) Bangkok, Hanoi, Jakarta</option>
					<option value="8.0">(GMT +8:00) Beijing, Perth, Singapore, Hong Kong</option>
					<option value="9.0">(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>
					<option value="9.5">(GMT +9:30) Adelaide, Darwin</option>
					<option value="10.0">(GMT +10:00) Eastern Australia, Guam, Vladivostok</option>
					<option value="11.0">(GMT +11:00) Magadan, Solomon Islands, New Caledonia</option>
					<option value="12.0">(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka</option>
				</select>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<div class="control-group <?php echo $this->get('error-password') ? 'error' : '' ?>">
			<label class="control-label" for="password-repeat">Current password</label>

			<div class="controls">
				<input id="password" name="password" class="input-xlarge" type="password">
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<button class="btn btn-primary" type="submit"><i class="icon-user icon-white"></i> Update account</button>
			</div>
		</div>
	</fieldset>
</form>

<?php require 'footer.html.php' ?>
