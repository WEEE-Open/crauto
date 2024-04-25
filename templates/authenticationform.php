<?php
/** @var $requireOldPassword bool */
?>
<form method="POST">
	<?php if ($requireOldPassword ?? true) : ?>
		<div class="form-group row">
			<label for="old-password" class="col-sm-2 col-form-label">Current password</label>
			<div class="col-sm-10">
				<input type="password" class="form-control" id="old-password" name="oldpassword" required>
			</div>
		</div>
	<?php endif ?>
	<div class="form-group row">
		<label for="auth-password1" class="col-sm-2 col-form-label">New password</label>
		<div class="col-sm-10">
			<input type="password" class="form-control" id="auth-password1" name="password1" aria-describedby="password1-help" minlength="<?= CRAUTO_PASS_MIN_LENGTH ?>" required>
			<small id="password1-help" class="form-text text-muted">
				Your password must be <strong>at least <?= CRAUTO_PASS_MIN_LENGTH ?> characters long</strong>. Choosing a passphrase is a <a href="https://xkcd.com/936/">good idea</a>, as it is using a password manager and a random password.
			</small>
		</div>
	</div>
	<div class="form-group row">
		<label for="auth-password2" class="col-sm-2 col-form-label">Confirm password</label>
		<div class="col-sm-10">
			<input type="password" class="form-control" id="auth-password2" name="password2" aria-describedby="password2-help" minlength="<?= CRAUTO_PASS_MIN_LENGTH ?>" required>
			<small id="password1-help" class="form-text text-muted">
				Repeat the same password as above.
			</small>
		</div>
	</div>
	<div class="form-group">
		<button type="submit" class="btn btn-primary">Change password</button>
	</div>
</form>