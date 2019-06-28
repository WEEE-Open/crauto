<?php

namespace WEEEOpen\Crauto;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

session_start();

if(isset($_SESSION['register_done'])) {
	unset($_SESSION['register_done']);
	$template = Template::create();
	echo $template->render('register_done');
} else {
	$template = Template::create();
	$template->addData(['authenticated' => Authentication::isLoggedIn(), 'isAdmin' => Authentication::isLoggedIn() && Authentication::isAdmin()], 'navbar');
	echo $template->render('403');
}

