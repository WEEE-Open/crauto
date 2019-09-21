<?php

namespace WEEEOpen\Crauto;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();

$template = Template::create();
$template->addData(['currentSection' => 'sessions'], 'navbar');
echo $template->render('sessions');
