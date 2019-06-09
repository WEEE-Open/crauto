<?php

namespace WEEEOpen\Crauto;

use InvalidArgumentException;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();

$allowedAttributes = [
	'uid' => 'Username',
	'cn' => 'Full name',
	'givenname' => 'Name',
	'sn' => 'Surname',
	'memberof' => 'Groups',
	'mail' => 'Email',
	'schacpersonaluniquecode' => 'Student ID',
	'degreecourse' => 'Degree course',
	'schacdateofbirth' => 'Date of birth',
	'schacplaceofbirth' => 'Place of birth',
	'mobile' => 'Cellphone',
	'safetytestdate' => 'Date of the test on safety',
	'telegramid' => 'Telegram ID',
	'telegramnickname' => 'Telegram nickname',
	'sshpublickey' => 'SSH public keys',
	//'description' => 'Notes'
];
$editableAttributes = ['mail', 'schacpersonaluniquecode', 'degreecourse', 'telegramid', 'telegramnickname'];
$editableAttributes = array_combine($editableAttributes, $editableAttributes);

$attributes = [];
$error = null;
try {
	$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN,
		CRAUTO_LDAP_GROUPS_DN, false);
	$attributes = $ldap->getUser($_SESSION['uid'], array_keys($allowedAttributes));

	if(isset($_POST) && !empty($_POST)) {
		$edited = array_intersect_key($_POST, $editableAttributes);
		$edited = Validation::normalize($ldap, $edited);
		Validation::validate($edited);
		$ldap->updateUser($_SESSION['uid'], $edited, $attributes);
		http_response_code(303);
		header("Location: /personal.php");
		exit(0);
	}
} catch(LdapException $e) {
	$error = $e->getMessage();
} catch(InvalidArgumentException $e) {
	$error = $e->getMessage();
} catch(ValidationException $e) {
	$error = $e->getMessage();
}

$groups = [];
foreach($attributes['memberof'] as $dn) {
	$groups[] = Ldap::groupDnToName($dn);
}
$attributes['memberof'] = $groups;
$attributes['safetytestdate'] = isset($attributes['safetytestdate']) ? Validation::dateSchacToHtml($attributes['safetytestdate']) : null;
$attributes['schacdateofbirth'] = isset($attributes['schacdateofbirth']) ? Validation::dateSchacToHtml($attributes['schacdateofbirth']) : null;

$template = Template::create();
$template->addData(['currentSection' => 'personal'], 'navbar');
echo $template->render('user', [
	'error' => $error,
	'attributes' => $attributes,
	'attributeNames' => $allowedAttributes,
	'editableAttributes' => $editableAttributes,
]);
