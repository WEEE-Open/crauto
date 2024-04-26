<?php

namespace WEEEOpen\Crauto;

use InvalidArgumentException;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();
if (!Authentication::isAdmin()) {
	$template = Template::create();
	echo $template->render('403');
	exit;
}

if (isset($_GET['uid'])) {
	$targetUid = $_GET['uid'];
	$getTheseAttributes = [
		'givenname',
		'sn',
		'schacpersonaluniquecode',
		'degreecourse',
		'safetytestdate',
	];

	try {
		$ldap = new Ldap(
			CRAUTO_LDAP_URL,
			CRAUTO_LDAP_BIND_DN,
			CRAUTO_LDAP_PASSWORD,
			CRAUTO_LDAP_USERS_DN,
			CRAUTO_LDAP_GROUPS_DN,
			CRAUTO_LDAP_STARTTLS
		);
		$attributes = $ldap->getUser($targetUid, $getTheseAttributes);
		if ($attributes === null) {
			$template = Template::create();
			echo $template->render('404');
			exit;
		}

		foreach ($getTheseAttributes as $name) {
			if (!array_key_exists($name, $attributes)) {
				throw new InvalidArgumentException("$name is required");
			}
			$attr = $attributes[$name];
			if (is_array($attr) && count($attr) <= 0) {
				throw new InvalidArgumentException("$name is empty (and multivalued)");
			}
			if ($attr === null || $attr === '') {
				throw new InvalidArgumentException("$name is empty");
			}
		}

		$safetytestdate = Validation::dateSchacToHtml($attributes['safetytestdate']);

		$replace = [
			'[NAME]'     => $attributes['givenname'],
			'[SURNAME]'  => $attributes['sn'],
			'[ID]'       => $attributes['schacpersonaluniquecode'],
			'[TESTDATE]' => $safetytestdate,
			'[CDL]'      => $attributes['degreecourse'],
		];

		$sir = new Sir(__DIR__ . '/../resources/pdftemplates');
		$pdf = $sir->getSir($targetUid, $replace);
		header('Content-type: application/pdf');
		header("Content-Disposition: attachment; filename=\"sir-$targetUid.pdf\"");
		readfile($pdf);

		exit;
	} catch (LdapException | SirException | ValidationException | InvalidArgumentException $e) {
		$error = $e->getMessage();
	}

	if ($error !== null) {
		$template = Template::create();
		echo $template->render('500', [
			'error' => $error,
		]);

		exit;
	}
} else {
	$template = Template::create();
	echo $template->render('400', ['error' => 'For which user?']);

	exit;
}
