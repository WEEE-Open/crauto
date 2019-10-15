#!/bin/php
<?php

use WEEEOpen\Crauto\Sir;
use WEEEOpen\Crauto\SirException;

include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Sir.php';
include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'SirException.php';

if($argc !== 3) {
	$program = $argv[0];
	echo 'Usage: pasta.php /path/to/input/files /path/to/output';
	exit(1);
}

$dir = $argv[1];
$outputDir = $argv[2];

if(substr($dir, -1, 1) !== '/' && substr($dir, -1, 1) !== DIRECTORY_SEPARATOR) {
	$dir = $dir . '/';
}

try {
	$sir = new Sir($outputDir);
	unset($outputDir);
} catch(SirException $e) {
	echo 'SirException: ' . $e->getMessage() . PHP_EOL;
	exit(1);
}


if(!file_exists("${dir}template.tex")) {
	echo 'No template.tex file' . PHP_EOL;
	exit(1);
}
if(!file_exists("${dir}data.csv")) {
	echo 'No data.csv file' . PHP_EOL;
	exit(1);
}
$template = file_get_contents("${dir}template.tex");
if($template === false) {
	echo 'Cannot read template.tex' . PHP_EOL;
	exit(1);
}
$data = file_get_contents("${dir}data.csv");
if($data === false) {
	echo 'Cannot read data.csv' . PHP_EOL;
	exit(1);
}
$lines = preg_split('/[\n\r]/', $data, -1, PREG_SPLIT_NO_EMPTY);
if(count($lines) <= 0) {
	echo 'Empty data.csv' . PHP_EOL;
	exit(1);
}
if(count($lines) <= 1) {
	echo 'Only one line (header) in data.csv' . PHP_EOL;
	exit(1);
}
$header = array_shift($lines);
$replace = explode(',', $header);
foreach($replace as &$value) {
	$value = '[' . $value . ']';
	if(strstr($template, $value) === false) {
		echo "$value not found in template" . PHP_EOL;
		exit(1);
	}
}

$fullname = in_array('[NAME]', $replace) && in_array('[SURNAME]', $replace) && in_array('[ID]', $replace);
foreach($lines as $lineno => $oneline) {
	$pieces = explode(',', $oneline);
	$replacements = array_combine($replace, $pieces);

	if($fullname) {
		$filename = 'SIR ' . $replacements['[SURNAME]'] . ' ' . $replacements['[NAME]'] . ' ' . $replacements['[ID]'];
	} else {
		$filename = 'SIR ' . ($lineno + 1);
	}

	$pdf = $sir->getSir($filename, $template, $replacements);
	$outputDir = $sir->getDirectory();
	if(!rename($pdf, $outputDir . substr($pdf, strrpos($pdf, DIRECTORY_SEPARATOR) + 1))) {
		echo PHP_EOL . "Cannot move files to $outputDir" . PHP_EOL;
		exit(1);
	}
}
