<?php

namespace WEEEOpen\Crauto;

use League\Plates\Engine as Plates;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();

$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN, CRAUTO_LDAP_GROUPS_DN, false);
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
$attributes = $ldap->getInfo($_SESSION['uid'], array_keys($allowedAttributes));

// Additional safeguard, possibly useless
$attributes = array_intersect_key($attributes, $allowedAttributes);

$groups = [];
foreach($attributes['memberof'] as $dn) {
	$groups[] = Ldap::groupDnToName($dn);
}
$attributes['memberof'] = $groups;

$templates = new Plates('..' . DIRECTORY_SEPARATOR . 'templates');
echo $templates->render('user', [
	'uid' => $_SESSION['uid'],
	'name' => $_SESSION['cn'],
	'attributes' => $attributes,
	'attributeNames' => $allowedAttributes,
	'editableAttributes' => $editableAttributes,
]);
