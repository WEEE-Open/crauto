<?php
/** @var $uid string */
/** @var $name string */
/** @var $error ?string */
$this->layout('base', ['title' => 'Register']) ?>

<h1>Register</h1>
<div class="alert alert-success" role="alert">
	Your account has been created
</div>
<p>However it <strong>needs to be activated by an administrator</strong> before you can sign-in.</p>
<p>If you try to log in, it will appear as it doesn't exist but trust me: it has been created.</p>
