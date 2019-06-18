<?php

namespace WEEEOpen\Crauto;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$template = Template::create();
$loggedin = Authentication::isLoggedIn();
$template->addData(['authenticated' => $loggedin, 'isAdmin' => $loggedin && Authentication::isAdmin()], 'navbar');
echo $template->render('tos');
