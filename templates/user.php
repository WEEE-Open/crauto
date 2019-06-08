<?php
/** @var $uid string */
/** @var $name string */
/** @var $error string|null */
/** @var $attributes array */
/** @var $attributeNames string[] */
/** @var $editableAttributes string[] */
$this->layout('base', ['title' => 'Index']);
$editable = function(string $attr) use ($editableAttributes): string {
	return isset($editableAttributes[$attr]) ? '' : 'readonly';
};
$type = function(string $attr): string {
	switch($attr) {
		case 'mail':
			return 'email';
		case 'telegramid':
		case 'schacpersonaluniquecode':
			return 'number';
		case 'mobile':
			return 'tel';
		case 'safetytestdate':
			return 'date';
		default:
			return 'text';
	}
};
$validation = function(string $attr): string {
	switch($attr) {
		case 'telegramid':
		case 'schacpersonaluniquecode':
			return 'min="1"';
		case 'uid':
			return 'pattern="^[a-zA-Z][a-zA-Z0-9-_\.]*$"';
		case 'schacPlaceOfBirth':
			return 'pattern="\w+(\s\(\w+\))?,\s*\w+"';
		default:
			return '';
	}
}
?>

<h1>Personal profile</h1>

<?php if($error !== null): ?>
<div class="alert alert-danger" role="alert">
Error: <?= $error ?>
</div>
<?php endif ?>

<form method="POST" target="/personal.php">
	<?php foreach($attributes as $attr => $values): ?>
	<?php if(is_array($values)): ?>
		<div class="form-group">
			<label for="profile-<?= $attr ?>"><?= $attributeNames[$attr] ?></label>
			<textarea class="form-control" id="profile-<?= $attr ?>" name="<?= $attr ?>" rows="<?= count($values) + 1 ?>" <?= $editable($attr) ?> <?= $validation($attr) ?>><?= implode("\r\n", array_map([$this, 'e'], $values)) . "\r\n" ?></textarea>
		</div>
	<?php else: ?>
		<div class="form-group">
			<label for="profile-<?= $attr ?>"><?= $attributeNames[$attr] ?></label>
			<input type="<?= $type($attr) ?>" class="form-control" id="profile-<?= $attr ?>" name="<?= $attr ?>" value="<?= $values === null ? '' : $this->e($values) ?>" <?= $editable($attr) ?> <?= $validation($attr) ?>>
		</div>
	<?php endif; ?>
	<?php endforeach; ?>
	<button type="submit" class="btn btn-primary">Submit</button>
</form>
