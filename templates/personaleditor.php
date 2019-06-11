<?php
/** @var $error string|null */
/** @var $attributes array */
/** @var $editableAttributes string[] */
/** @var $allowedAttributes string[] */
$title = 'Personal profile';
$this->layout('base', ['title' => $title]);
?>

<h1><?= $title ?></h1>

<?php if($error !== null): ?>
	<div class="alert alert-danger" role="alert">
		Error: <?= $this->e($error) ?>
	</div>
<?php endif ?>

<?= $this->fetch('userform', ['attributes' => $attributes, 'editableAttributes' => $editableAttributes, 'allowedAttributes' => $allowedAttributes, 'target' => 'personal.php']) ?>

<p>
	<a class="btn btn-secondary" href="/personal.php?download" role="button">Download this data</a> and admire our GDPR compliance!
</p>
