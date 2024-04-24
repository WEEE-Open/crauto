#!/bin/php
<?php

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once '../config/config.php';

$dirs = [
	__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'photos',
	__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'latex' . DIRECTORY_SEPARATOR . 'sir',
	__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'pdftemplates' . DIRECTORY_SEPARATOR . 'sir',
];
foreach($dirs as $dir) {
	$files = scandir($dir);
	foreach($files as $file) {
		$when = fileatime($file);
		if(time() + 60 * 60 * 24 * CRAUTO_SIR_TMP_DIR_CLEANUP > $when) {
			unlink($file);
		}
	}
}
