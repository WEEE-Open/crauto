<?php
/** @var $uid string */
/** @var $name string */
/** @var $attributes array */
/** @var $attributeNames string[] */
$this->layout('base', ['title' => 'Index']);
?>

<h1>Profile</h1>

<form>
	<?php foreach($attributes as $attr => $values): ?>
	<div class="form-group">
		<label for="profile-<?= $attr ?>"><?= $attributeNames[$attr] ?></label>
		<input type="text" class="form-control" id="profile-<?= $attr ?>" value="<?= $this->e($values) ?>">
	</div>
	<?php endforeach; ?>
	<button type="submit" class="btn btn-primary">Submit</button>
</form>
