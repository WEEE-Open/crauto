<?php

namespace WEEEOpen\Crauto;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$template = Template::create();
$template->addData(['authenticated' => false], 'navbar');
echo $template->render('logout');
