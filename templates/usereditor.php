<?php
/** @var $title string */
/** @var $error string|null */
/** @var $adminRequireOldPassword bool */
/** @var $target string */
/** @var $attributes array */
/** @var $editableAttributes string[] */
$this->layout('base');
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

<?= $this->fetch('userform', ['attributes' => $attributes, 'editableAttributes' => $editableAttributes, 'allowedAttributes' => $allowedAttributes, 'target' => $target]) ?>
<?= $this->fetch('authenticationform', ['requireOldPassword' => $adminRequireOldPassword, 'target' => $target]) ?>
