<?php
/** @var $uid string */
/** @var $name string */
/** @var $error ?string */
http_response_code(505);
$this->layout('base', ['title' => 'Internal Server Error']) ?>

<h1>500 - Internal Server Error</h1>
<?php if(isset($error) && $error !== null): ?>
	<div class="alert alert-danger" role="alert">
		<?= $this->e($error) ?>
	</div>
<?php endif ?>
