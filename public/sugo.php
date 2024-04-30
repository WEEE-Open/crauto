<?php

namespace WEEEOpen\Crauto;

use DateTimeZone;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();



$ldap = new Ldap(
	CRAUTO_LDAP_URL,
	CRAUTO_LDAP_BIND_DN,
	CRAUTO_LDAP_PASSWORD,
	CRAUTO_LDAP_USERS_DN,
	CRAUTO_LDAP_GROUPS_DN,
	CRAUTO_LDAP_STARTTLS
);

$users = [];
$selectedUser = null;
if (Authentication::isAdmin()) {
	$ldap = new Ldap(
		CRAUTO_LDAP_URL,
		CRAUTO_LDAP_BIND_DN,
		CRAUTO_LDAP_PASSWORD,
		CRAUTO_LDAP_USERS_DN,
		CRAUTO_LDAP_GROUPS_DN,
		CRAUTO_LDAP_STARTTLS
	);
	$users = $ldap->getUsers(['givenname','sn','signedsir','nsaccountlock', 'mail']);
	if (isset($_GET['uid'])) {
		$selectedUser = $_GET['uid'];
	}
} else {
	$users = [$ldap->getUser($_SESSION['uid'], ['givenname','sn','signedsir','nsaccountlock', 'mail'])];
	$selectedUser = $_SESSION['uid'];
}

$mappedUsers = [];
foreach ($users as $user) {
	$mappedUsers[] = [
		'id' => $user['uid'],
		'name' => $user['givenname'] . ' ' . $user['sn'],
		'needsToSign' => !($user['signedsir'] ?? false),
		'isBlocked' => !($user['nsaccountlock'] ?? false),
		'email' => $user['mail']
	];
}

$template = Template::create();
$template->addData(['currentSection' => 'sugo'], 'navbar');

echo $template->render('sugo', [
	'users' => $mappedUsers,
	'selectedUser' => $selectedUser
]);