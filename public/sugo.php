<?php

namespace WEEEOpen\Crauto;

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

$selectedUser = null;
if (Authentication::isAdmin()) {
	$users = $ldap->getUsers(['uid', 'cn', 'givenname', 'sn', 'signedsir', 'nsaccountlock', 'mail']);
	if (isset($_GET['uid'])) {
		$selectedUser = $_GET['uid'];
	}
} else {
	$users = [$ldap->getUser($_SESSION['uid'], ['uid', 'cn', 'givenname', 'sn', 'signedsir', 'nsaccountlock', 'mail'])];
	$selectedUser = $_SESSION['uid'];
}

$mappedUsers = [];
foreach ($users as $user) {
	$mappedUsers[] = [
		'uid' => $user['uid'],
		'cn' => $user['cn'],
		'needsToSign' => Ldap::optionalBooleanToBool($user, 'signedsir'),
		'isLocked' => Ldap::optionalBooleanToBool($user, 'nsaccountlock'),
		'email' => $user['mail']
	];
}

usort($mappedUsers, function (array $a, array $b): int {
	return strcasecmp($a['uid'], $b['uid']);
});

$template = Template::create();
$template->addData(['currentSection' => 'sugo'], 'navbar');

echo $template->render('sugo', [
	'users' => $mappedUsers,
	'selectedUser' => $selectedUser
]);
