<?php
/** @var $uid string */
/** @var $name string */
/** @var $attributes array */
$this->layout('base', ['title' => 'Index']);
$allowedAttributes = [
	'uid' => 'Username',
	'cn' => 'Full name',
	'mail' => 'Email'
]

?>

<h1>Profile</h1>

<form>
	<?php foreach($attributes as $attr => $values): ?>
	<div class="form-group">
		<label for="profile-<?= $attr ?>"><?= $attr ?></label>
		<input type="text" class="form-control" id="profile-<?= $attr ?>" value="<?= $this->e($values) ?>">
	</div>
	<?php endforeach; ?>
	<button type="submit" class="btn btn-primary">Submit</button>
</form>
