<?php
/** @var $error string|null */
/** @var $attributes array */
/** @var $degreeCourses string[] */
/** @var $countries string[] */
/** @var $province string[] */
$this->layout('base', ['title' => 'Register']);
?>

<h1>Register</h1>

<?php if($error !== null): ?>
	<div class="alert alert-danger" role="alert">
		Error: <?= $this->e($error) ?>
	</div>
<?php endif ?>

<?php if(!isset($attributes['telegramid']) && !isset($attributes['telegramnickname'])): ?>
	<div class="alert alert-warning" role="alert">
		Copy and paste the address of this page to <a href="https://telegram.me/weeelab_bot" target="_blank">@weeelab_bot</a> and reload the page to fill the Telegram ID field automatically.
	</div>
<?php endif ?>

<form method="POST" action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']; ?>">
	<div class="form-group row">
		<label for="profile-uid" class="col-sm-2 col-form-label">Username</label>
		<div class="col-md-10">
			<input type="text" class="form-control" id="profile-uid" name="uid" value="<?= $this->e($attributes['uid'] ?? '') ?>" pattern="^[a-zA-Z][a-zA-Z0-9-_\.]*$" maxlength="50"  aria-describedby="uid-help" required>
			<small id="uid-help" class="form-text text-muted">
				You will use this username to sign in and you won't be able to change it later. Unless you ask nicely the sysadmin, that is. Case-insensitive, must begin with a letter, only alphanumeric, -, _ and . are allowed.
			</small>
		</div>
	</div>
	<div class="form-group row">
		<label for="auth-password1" class="col-sm-2 col-form-label">Password</label>
		<div class="col-md-10">
			<input type="password" class="form-control" id="auth-password1" name="password1" aria-describedby="password1-help" minlength="<?= CRAUTO_PASS_MIN_LENGTH ?>" required>
			<small id="password1-help" class="form-text text-muted">
				Your password must be <strong>at least <?= CRAUTO_PASS_MIN_LENGTH ?> characters long</strong>. Choosing a passphrase is a <a href="https://xkcd.com/936/">good idea</a>, as it is using a password manager and a random password.
			</small>
		</div>
	</div>
	<div class="form-group row">
		<label for="auth-password2" class="col-sm-2 col-form-label">Confirm password</label>
		<div class="col-md-10">
			<input type="password" class="form-control" id="auth-password2" name="password2" aria-describedby="password2-help" minlength="<?= CRAUTO_PASS_MIN_LENGTH ?>" required>
			<small id="password1-help" class="form-text text-muted">
				Repeat the same password as above.
			</small>
		</div>
	</div>
	<div class="form-row form-group">
		<div class="col-sm-6">
			<label for="profile-telegramid">Telegram ID (optional)</label>
			<input type="number" class="form-control" id="profile-telegramid" name="telegramid" value="<?= $this->e($attributes['telegramid'] ?? '') ?>" min="0" maxlength="500">
		</div>
		<div class="col-sm-6">
			<label for="profile-telegramnickname">Telegram nickname (optional)</label>
			<div class="input-group mb-3">
				<div class="input-group-prepend">
					<span class="input-group-text" id="telegramnickname-addon">@</span>
				</div>
				<input type="text" class="form-control" id="profile-telegramnickname" aria-describedby="telegramnickname-addon" name="telegramnickname" value="<?= $this->e($attributes['telegramnickname'] ?? '') ?>" maxlength="500">
			</div>
		</div>
	</div>
	<hr>
	<div class="form-group">
		<small id="data-help" class="form-text text-muted">
			Type these informations as they appear in any official place, e.g. your student ID card, the "<a href="https://didattica.polito.it/" target="_blank">portale della didattica</a>", and so on. If you have multiple first names and want just the first one displayed, or if you prefer a nickname, ask an admin.
		</small>
	</div>
	<div class="form-row">
		<div class="form-group col-sm-6">
			<label for="profile-givenname">Name</label>
			<input type="text" class="form-control" id="profile-givenname" name="givenname" value="<?= $this->e($attributes['givenname'] ?? '') ?>" maxlength="500" required>
		</div>
		<div class="form-group col-sm-6">
			<label for="profile-sn">Surname</label>
			<input type="text" class="form-control" id="profile-sn" name="sn" value="<?= $this->e($attributes['sn'] ?? '') ?>" maxlength="500" required>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-sm-6">
			<label for="profile-degreecourse">Degree course</label>
			<select class="form-control" id="profile-degreecourse" name="degreecourse" required>
				<option value="" disabled hidden <?= isset($attributes['degreecourse']) ? '' : 'selected'  ?>></option>
				<?php foreach($degreeCourses as $course): ?>
					<option value="<?= $this->e($course) ?>" <?= $attributes['degreecourse'] === $course ? 'selected' : '' ; ?>><?= $this->e($course) ?></option>
				<?php endforeach ?>
			</select>
		</div>
		<div class="form-group col-sm-6">
			<label for="profile-schacpersonaluniquecode">Student ID (matricola)</label>
			<input type="text" class="form-control" id="profile-schacpersonaluniquecode" placeholder="s123456" minlength="2" name="schacpersonaluniquecode" value="<?= $this->e($attributes['schacpersonaluniquecode'] ?? '') ?>" pattern="(s|d|S|D)?\d+" maxlength="500" required>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-sm-6">
			<label for="profile-mail">Email address (optional)</label>
			<input type="email" class="form-control" id="profile-mail" name="mail" value="<?= $this->e($attributes['mail'] ?? '') ?>" maxlength="500">
		</div>
		<div class="form-group col-sm-6">
			<label for="profile-schacdateofbirth">Date of birth (optional)</label>
			<input type="date" class="form-control" id="profile-schacdateofbirth" placeholder="1996-01-30" pattern="\d{4}-\d{2}-\d{2}" name="schacdateofbirth" value="<?= $this->e($attributes['schacdateofbirth'] ?? '') ?>" maxlength="500" required>
		</div>
	</div>
	<div class="form-row">
		<p>Read the <a href="tos.php" target="_blank">Informazioni sul trattamento dei dati personali (privacy policy)</a></p>
	</div>
	<div class="form-row">
		<div class="form-group form-check">
			<input type="checkbox" class="form-check-input" id="register-tos" name="register-tos">
			<label class="form-check-label" for="register-tos">I have read and accept "Informazioni sul trattamento dei dati personali (privacy policy)"</label>
		</div>
	</div>
	<div class="form-group">
		<input type="submit" class="btn btn-primary" value="Save">
	</div>
</form>

