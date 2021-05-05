<?php

namespace WEEEOpen\Crauto;

use InvalidArgumentException;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();
if(!Authentication::isAdmin()) {
	$template = Template::create();
	echo $template->render('403');
	exit;
}

if(isset($_GET['uid'])) {
	$targetUid = $_GET['uid'];
	$getTheseAttributes = [
		'givenname',
		'sn',
		'schacpersonaluniquecode',
		'degreecourse',
		'schacdateofbirth',
		'schacplaceofbirth',
		'mobile',
		'safetytestdate',
	];

	try {
		$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN,
			CRAUTO_LDAP_GROUPS_DN, CRAUTO_LDAP_STARTTLS);
		$attributes = $ldap->getUser($targetUid, $getTheseAttributes);
		if($attributes === null) {
			$template = Template::create();
			echo $template->render('404');
			exit;
		}

		foreach($attributes as $name => $attr) {
			if(is_array($attr) && count($attr)) {
				throw new InvalidArgumentException("$name is empty (and multivalued)");
			}
			if($attr === null || $attr === '') {
				throw new InvalidArgumentException("$name is empty");
			}
		}

		list($yyyy, $mm, $dd) = explode('-', Validation::dateSchacToHtml($attributes['schacdateofbirth']), 3);
		$safetytestdate = Validation::dateSchacToHtml($attributes['safetytestdate']);
		list($place, $country) = explode(', ', $attributes['schacplaceofbirth'], 2);

		$replace = [
			'[NAME]'     => Sir::escapeString($attributes['givenname']),
			'[SURNAME]'  => Sir::escapeString($attributes['sn']),
			'[ID]'       => Sir::escapeString($attributes['schacpersonaluniquecode']),
			'[DD]'       => Sir::escapeString($dd),
			'[MM]'       => Sir::escapeString($mm),
			'[YYYY]'     => Sir::escapeString($yyyy),
			'[PLACE]'    => str_replace(' ', '\\\\', Sir::escapeString($place)),
			'[COUNTRY]'  => Sir::escapeString($country),
			'[MOBILE]'   => Sir::escapeString($attributes['mobile']),
			'[EMAIL]'    => Sir::escapeString(Sir::politoMail($attributes['schacpersonaluniquecode'])),
			'[TESTDATE]' => Sir::escapeString($safetytestdate),
			'[CDL]'      => Sir::escapeString($attributes['degreecourse']),
		];

		$sir = new Sir(CRAUTO_SIR_TMP_DIR);
		if(substr(strtolower($attributes['degreecourse']), 0, 9) === 'dottorato') {
			$template = __DIR__ . '/../resources/latex/template-dottorandi.tex';
		} else {
			$template = __DIR__ . '/../resources/latex/template-studenti.tex';
		}
		$replace['[SIRPATH]'] = Sir::escapeString(__DIR__ . '/../resources/latex/T-MOD-SIR.pdf');
		$replace['[FMODPATH]'] = Sir::escapeString(__DIR__ . '/../resources/latex/F-MOD-LABORATORI.pdf');
		$filename = "sir-$targetUid-".sha1(var_export($replace, 1));
		$pdf = $sir->getSir($filename, file_get_contents($template), $replace);
		header('Content-type: application/pdf');
		header("Content-Disposition: attachment; filename=\"sir-$targetUid.pdf\"");
		readfile($pdf);

		exit;

	} catch(LdapException | SirException | ValidationException | InvalidArgumentException $e) {
		$error = $e->getMessage();
	}

	if($error !== null) {
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


