<?php
/** @var $uid string */
/** @var $name string */
/** @var $attributes array */
/** @var $attributeNames string[] */
/** @var $editableAttributes string[] */
$this->layout('base', ['title' => 'Index']);
$editableAttributes = array_combine($editableAttributes, $editableAttributes);
$editable = function(string $attr) use ($editableAttributes): string {
	return isset($editableAttributes[$attr]) ? '' : 'readonly';
}
?>

<h1>Personal profile</h1>

<form>
	<?php foreach($attributes as $attr => $values): ?>
	<?php if(is_array($values)): ?>
		<div class="form-group">
			<label for="profile-<?= $attr ?>"><?= $attributeNames[$attr] ?></label>
			<textarea class="form-control" id="profile-<?= $attr ?>" rows="<?= count($values) + 1 ?>"<?= $editable($attr) ?>><?= implode("\r\n", array_map([$this, 'e'], $values)) . "\r\n" ?></textarea>
		</div>
	<?php else: ?>
		<div class="form-group">
			<label for="profile-<?= $attr ?>"><?= $attributeNames[$attr] ?></label>
			<input type="text" class="form-control" id="profile-<?= $attr ?>" value="<?= $values === null ? '' : $this->e($values) ?>"<?= $editable($attr) ?>>
		</div>
	<?php endif; ?>
	<?php endforeach; ?>
	<button type="submit" class="btn btn-primary">Submit</button>
</form>
