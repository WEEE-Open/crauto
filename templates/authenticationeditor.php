<?php
/** @var $error string|null */
/** @var $success string */
$this->layout('base', ['title' => 'Authentication']);
?>

<h1>Authentication</h1>

<?php if ($error !== null) : ?>
<div class="alert alert-danger" role="alert">
Error: <?= $this->e($error) ?>
</div>
<?php endif ?>
<?php if ($success !== null) : ?>
	<div class="alert alert-success" role="alert">
		<?= $this->e($success) ?>
	</div>
<?php endif ?>

<?= $this->fetch('authenticationform', ['requireOldPassword' => true]) ?>
