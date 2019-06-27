<?php
/** @var $uid string */
/** @var $name string */
/** @var $error ?string */
$this->layout('base', ['title' => 'Forbidden']) ?>

<h1>403 - Forbidden</h1>
<p>You are not authorized to access this page</p>
<?php if(isset($error) && $error !== null): ?>
	<div class="alert alert-danger" role="alert">
		<?= $this->e($error) ?>
	</div>
<?php endif ?>

