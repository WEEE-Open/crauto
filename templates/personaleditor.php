<?php
/** @var $error string|null */
/** @var $target string */
/** @var $attributes array */
/** @var $editableAttributes string[] */
$title = 'Personal profile';
$this->layout('base', ['title' => $title]);
?>

<h1><?= $title ?></h1>

<?php if($attributes['nsaccountlock'] === 'true'): ?>
	<div class="alert alert-warning" role="alert">
		🔒&nbsp;This account is locked
	</div>
<?php endif ?>

<?php if($error !== null): ?>
	<div class="alert alert-danger" role="alert">
		Error: <?= $this->e($error) ?>
	</div>
<?php endif ?>

<?= $this->fetch('userform', ['attributes' => $attributes, 'editableAttributes' => $editableAttributes, 'target' => $target]) ?>
