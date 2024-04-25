<?php

namespace WEEEOpen\Crauto;

use DateTimeZone;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();
if (!Authentication::isAdmin()) {
	$template = Template::create();
	echo $template->render('403');
	exit;
}

$error = null;
$users = [];
try {
	$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN, CRAUTO_LDAP_GROUPS_DN, CRAUTO_LDAP_STARTTLS);
	$users = $ldap->getUsersList(new DateTimeZone('Europe/Rome'));
} catch (LdapException $e) {
	$error = $e->getMessage();
}

$template = Template::create();
$template->addData(['currentSection' => 'groups'], 'navbar');
echo $template->render('grouplist', [
	'users' => $users,
	'error' => $error,
]);
