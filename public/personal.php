<?php

namespace WEEEOpen\Crauto;

use League\Plates\Engine as Plates;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();

$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN, CRAUTO_LDAP_GROUPS_DN, false);
$attributes = $ldap->getInfo($_SESSION['uid']);

$allowedAttributes = [
	'uid' => 'Username',
	'cn' => 'Full name',
	'mail' => 'Email'
];
$attributes = array_intersect_key($attributes, $allowedAttributes);

$templates = new Plates('..' . DIRECTORY_SEPARATOR . 'templates');
echo $templates->render('user', [
	'uid' => $_SESSION['uid'],
	'name' => $_SESSION['cn'],
	'attributes' => $attributes,
	'attributeNames' => $allowedAttributes
]);
