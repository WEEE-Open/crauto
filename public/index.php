<?php

namespace WEEEOpen\Crauto;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();

$template = Template::create();
$template->addData(['currentSection' => 'index'], 'navbar');
echo $template->render('index', [
	'uid' => $_SESSION['uid'],
	'id' => $_SESSION['id'],
	'name' => $_SESSION['cn'],
	'hasSignedSIR' => $_SESSION['signedsir'],
]);
