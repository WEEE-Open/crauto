<?php

namespace WEEEOpen\Crauto;

use InvalidArgumentException;
use League\Plates\Engine as Plates;

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
		$ldap->updateUser($_SESSION['uid'], $edited, $attributes);
		http_response_code(303);
		header("Location: /personal.php");
		exit(0);
	}

	$groups = [];
	foreach($attributes['memberof'] as $dn) {
		$groups[] = Ldap::groupDnToName($dn);
	}
	$attributes['memberof'] = $groups;
} catch(LdapException $e) {
	$error = $e->getMessage();
} catch(InvalidArgumentException $e) {
	$error = $e->getMessage();
}

$template = Template::create();
$template->addData(['currentSection' => 'personal'], 'navbar');
echo $template->render('user', [
	'uid' => $_SESSION['uid'],
	'name' => $_SESSION['cn'],
	'error' => $error,
	'attributes' => $attributes,
	'attributeNames' => $allowedAttributes,
	'editableAttributes' => $editableAttributes,
]);
