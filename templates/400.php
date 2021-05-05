<?php
/** @var $uid string */
/** @var $name string */
/** @var $error ?string */
http_response_code(400);
$this->layout('base', ['title' => 'Bad Request']) ?>

<h1>400 - Bad Request</h1>
<?php if(isset($error)): ?>
	<div class="alert alert-danger" role="alert">
		<?= $this->e($error) ?>
	</div>
<?php endif ?>
