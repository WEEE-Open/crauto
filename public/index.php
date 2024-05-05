<?php

namespace WEEEOpen\Crauto;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();

$error = null;
$attributes = [];
try {
	$ldap = new Ldap(
		CRAUTO_LDAP_URL,
		CRAUTO_LDAP_BIND_DN,
		CRAUTO_LDAP_PASSWORD,
		CRAUTO_LDAP_USERS_DN,
		CRAUTO_LDAP_GROUPS_DN,
		CRAUTO_LDAP_STARTTLS
	);
	$attributes = $ldap->getUser($_SESSION['uid'], ['signedsir']);
} catch (LdapException $e) {
	$error = $e->getMessage();
}

$template = Template::create();
$template->addData(['currentSection' => 'index'], 'navbar');
echo $template->render('index', [
	'error' => $error,
	'uid' => $_SESSION['uid'],
	'id' => $_SESSION['id'],
	'name' => $_SESSION['cn'],
	'signedSir' => isset($attributes['signedsir']) && $attributes['signedsir'] === 'true',
]);
