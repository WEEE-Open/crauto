<?php
/** @var $uid string */
/** @var $name string */
/** @var $error ?string */
http_response_code(500);
$this->layout('base', ['title' => 'Internal Server Error']) ?>

<h1>500 - Internal Server Error</h1>
<?php if (isset($error)) : ?>
	<div class="alert alert-danger" role="alert">
		<pre><?= $this->e($error) ?></pre>
	</div>
<?php endif ?>
