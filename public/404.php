<?php

namespace WEEEOpen\Crauto;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$template = Template::create();
echo $template->render('404');
