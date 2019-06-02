<?php

namespace WEEEOpen\Crauto;

use League\Plates\Engine as Plates;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin() || die();

$templates = new Plates('..' . DIRECTORY_SEPARATOR . 'templates');
echo $templates->render('index', [
	'uid' => $_SESSION['uid'],
	'name' => $_SESSION['cn']
]);
