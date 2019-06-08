<?php

namespace WEEEOpen\Crauto;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();

$template = Template::create();
echo $template->render('index', [
	'uid' => $_SESSION['uid'],
	'name' => $_SESSION['cn']
]);
