<?php
/** @var $attributes array */
/** @var $editableAttributes string[] */
/** @var $allGroups string[] */
/** @var $allowedAttributes string[] */
sort($allGroups);
?>
<?php
$editable = function (string $attr) use ($editableAttributes): string {
	return isset($editableAttributes[$attr]) ? '' : 'readonly';
};
$disabled = function (string $attr) use ($editableAttributes): string {
	return isset($editableAttributes[$attr]) ? '' : 'disabled';
};
$attributeNames = [
	'uid' => 'Username',
	'cn' => 'Full name',
	'givenname' => 'Name',
	'sn' => 'Surname',
	'memberof' => 'Groups',
	'mail' => 'Email',
	'schacpersonaluniquecode' => 'Student ID',
	'degreecourse' => 'Degree course',
	'schacdateofbirth' => 'Date of birth',
	'schacplaceofbirth' => 'Place of birth',
	'mobile' => 'Cellphone',
	'safetytestdate' => 'Date of the test on safety',
	'telegramid' => 'Telegram ID',
	'telegramnickname' => 'Telegram nickname',
	'sshpublickey' => 'SSH public keys',
	'weeelabnickname' => 'Nicknames for weeelab',
	'websitedescription' => 'Role for website',
	'description' => 'Notes',
	'nsaccountlock' => 'Account locked',
	'haskey' => 'Has a key to the lab',
	'signedsir' => 'SIR signed',
];
?>
<form method="POST">
	<div class="form-group">
		<label for="profile-uid"><?= $attributeNames['uid'] ?></label>
		<input type="text" class="form-control" id="profile-uid" name="uid" value="<?= $this->e($attributes['uid'] ?? '') ?>" <?= $editable('uid') ?> pattern="^[a-zA-Z][a-zA-Z0-9-_\.]*$" maxlength="50">
	</div>
	<div class="form-row">
		<div class="form-group col-sm-6">
			<label for="profile-givenname"><?= $attributeNames['givenname'] ?></label>
			<input type="text" class="form-control" id="profile-givenname" name="givenname" value="<?= $this->e($attributes['givenname'] ?? '') ?>" <?= $editable('givenname') ?> maxlength="500">
		</div>
		<div class="form-group col-sm-6">
			<label for="profile-sn"><?= $attributeNames['sn'] ?></label>
			<input type="text" class="form-control" id="profile-sn" name="sn" value="<?= $this->e($attributes['sn'] ?? '') ?>" <?= $editable('sn') ?> maxlength="500">
		</div>
	</div>
	<div class="form-group">
		<label for="profile-memberof"><?= $attributeNames['memberof'] ?></label>
		<select class="form-control selectpicker" multiple id="profile-memberof" name="memberof[]" <?= $editable('memberof') === '' ? '' : 'disabled' ?> data-size="6" data-container="body">
			<?php foreach ($allGroups as $group) : ?>
				<option <?= in_array($group, $attributes['memberof']) ? 'selected' : '' ?> value="<?=$this->e($group)?>"><?=$this->e($group)?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="form-row">
		<div class="form-group col-sm-6">
			<label for="profile-mail"><?= $attributeNames['mail'] ?></label>
			<input type="email" class="form-control" id="profile-mail" name="mail" value="<?= $this->e($attributes['mail'] ?? '') ?>" <?= $editable('mail') ?> maxlength="500">
		</div>
		<div class="form-group col-sm-6">
			<label for="profile-mobile"><?= $attributeNames['mobile'] ?></label>
			<input type="tel" class="form-control" id="profile-mobile" name="mobile" value="<?= $this->e($attributes['mobile'] ?? '') ?>" <?= $editable('mobile') ?> maxlength="500">
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-sm-6">
			<label for="profile-degreecourse"><?= $attributeNames['degreecourse'] ?></label>
			<input type="text" class="form-control" id="profile-degreecourse" name="degreecourse" value="<?= $this->e($attributes['degreecourse'] ?? '') ?>" <?= $editable('degreecourse') ?> maxlength="500">
		</div>
		<div class="form-group col-sm-6">
			<label for="profile-schacpersonaluniquecode"><?= $attributeNames['schacpersonaluniquecode'] ?></label>
			<input type="text" class="form-control" id="profile-schacpersonaluniquecode" placeholder="s123456" minlength="2" name="schacpersonaluniquecode" value="<?= $this->e($attributes['schacpersonaluniquecode'] ?? '') ?>" <?= $editable('schacpersonaluniquecode') ?> pattern="(s|d|S|D)?\d+" maxlength="500">
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-sm-6">
			<label for="profile-schacdateofbirth"><?= $attributeNames['schacdateofbirth'] ?></label>
			<input type="date" class="form-control" id="profile-schacdateofbirth" name="schacdateofbirth" placeholder="1996-01-30" pattern="\d{4}-\d{2}-\d{2}" value="<?= $this->e($attributes['schacdateofbirth'] ?? '') ?>" <?= $editable('schacdateofbirth') ?> maxlength="500">
		</div>
		<div class="form-group col-sm-6">
			<label for="profile-schacplaceofbirth"><?= $attributeNames['schacplaceofbirth'] ?></label>
			<input type="text" class="form-control" id="profile-schacplaceofbirth" name="schacplaceofbirth" value="<?= $this->e($attributes['schacplaceofbirth'] ?? '') ?>" <?= $editable('schacplaceofbirth') ?> maxlength="500" pattern="\w[\w\s]*(\([A-Za-z][A-Za-z]\))?, .+">
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-sm-6">
			<label for="profile-telegramid"><?= $attributeNames['telegramid'] ?></label>
			<input type="number" class="form-control" id="profile-telegramid" name="telegramid" value="<?= $this->e($attributes['telegramid'] ?? '') ?>" <?= $editable('telegramid') ?> min="0" maxlength="500">
		</div>
		<div class="form-group col-sm-6">
			<label for="profile-telegramnickname"><?= $attributeNames['telegramnickname'] ?></label>
			<div class="input-group mb-3">
				<div class="input-group-prepend">
					<span class="input-group-text" id="telegramnickname-addon">@</span>
				</div>
				<input type="text" class="form-control" id="profile-telegramnickname" aria-describedby="telegramnickname-addon" name="telegramnickname" value="<?= $this->e($attributes['telegramnickname'] ?? '') ?>" <?= $editable('telegramnickname') ?> maxlength="500">
			</div>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-sm-6">
			<label for="profile-safetytestdate"><?= $attributeNames['safetytestdate'] ?></label>
			<input type="date" class="form-control" id="profile-safetytestdate" name="safetytestdate" value="<?= $this->e($attributes['safetytestdate'] ?? '') ?>" <?= $editable('safetytestdate') ?>>
		</div>
	</div>
	<div class="form-group">
		<label for="profile-sshpublickey"><?= $attributeNames['sshpublickey'] ?></label>
		<textarea class="form-control" id="profile-sshpublickey" name="sshpublickey" rows="<?= count($attributes['sshpublickey']) + 1 ?>" <?= $editable('sshpublickey') ?> maxlength="10000"><?= implode("\r\n", array_map([$this, 'e'], $attributes['sshpublickey'])) ?></textarea>
	</div>
	<div class="form-group">
		<label for="profile-weeelabnickname"><?= $attributeNames['weeelabnickname'] ?></label>
		<textarea class="form-control" id="profile-weeelabnickname" name="weeelabnickname" rows="<?= count($attributes['weeelabnickname']) + 1 ?>" <?= $editable('weeelabnickname') ?> maxlength="500"><?= implode("\r\n", array_map([$this, 'e'], $attributes['weeelabnickname'])) ?></textarea>
	</div>
	<?php if (in_array('websitedescription', $allowedAttributes)) : ?>
		<div class="form-group">
			<label for="profile-description"><?= $attributeNames['websitedescription'] ?></label>
			<textarea class="form-control" id="profile-websitedescription" name="websitedescription" rows="1" <?= $editable('websitedescription') ?> maxlength="1000"><?= $this->e($attributes['websitedescription'] ?? '') ?></textarea>
		</div>
	<?php endif ?>
	<?php if (in_array('description', $allowedAttributes)) : ?>
		<div class="form-group">
			<label for="profile-description"><?= $attributeNames['description'] ?></label>
			<textarea class="form-control" id="profile-description" name="description" rows="<?= floor(strlen($attributes['description']) / 100 + 3) ?>" <?= $editable('description') ?> maxlength="10000"><?= $this->e($attributes['description'] ?? '') ?></textarea>
		</div>
	<?php endif ?>

	<?php if (in_array('nsaccountlock', $allowedAttributes) || in_array('signedsir', $allowedAttributes) || in_array('haskey', $allowedAttributes)) : ?>
	<div class="form-row">
		<?php if (in_array('nsaccountlock', $allowedAttributes)) : ?>
		<div class="form-group col-sm-6 col-md-4">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" value="true" id="profile-accountlock" <?= $attributes['nsaccountlock'] === 'true' ? 'checked' : '' ?> name="nsaccountlock" <?= $disabled('nsaccountlock') ?>>
				<label for="profile-accountlock"><?= $attributeNames['nsaccountlock'] ?></label>
			</div>
		</div>
		<?php endif ?>
		<?php if (in_array('signedsir', $allowedAttributes)) : ?>
		<div class="form-group col-sm-6 col-md-4">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" value="true" id="profile-signedsir" <?= $attributes['signedsir'] === 'true' ? 'checked' : '' ?> name="signedsir" <?= $disabled('signedsir') ?>>
				<label for="profile-signedsir"><?= $attributeNames['signedsir'] ?></label>
			</div>
		</div>
		<?php endif ?>
		<?php if (in_array('haskey', $allowedAttributes)) : ?>
		<div class="form-group col-sm-6 col-md-4">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" value="true" id="profile-haskey" <?= $attributes['haskey'] === 'true' ? 'checked' : '' ?> name="haskey" <?= $disabled('haskey') ?>>
				<label for="profile-haskey"><?= $attributeNames['haskey'] ?></label>
			</div>
		</div>
		<?php endif ?>
	</div>
	<?php endif ?>

	<div class="form-group">
		<button type="submit" class="btn btn-primary">Save</button>
	</div>
</form>