#!/bin/php
<?php

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

$dirs = [
	__DIR__ . '/../resources/pdftemplates/output',
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
