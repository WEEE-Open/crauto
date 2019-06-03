<?php

namespace WEEEOpen\Crauto;

use League\Plates\Engine as Plates;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$templates = new Plates('..' . DIRECTORY_SEPARATOR . 'templates');
echo $templates->render('logout');
