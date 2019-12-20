#!/bin/php
<?php
$dirs = [
	__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'photos',
	__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'latex' . DIRECTORY_SEPARATOR . 'sir',
];
foreach($dirs as $dir) {
	$files = scandir($dir);
	foreach($files as $file) {
		$when = fileatime($file);
		if(time() + 60 * 60 * 24 * 200 > $when) {
			unlink($file);
		}
	}
}
