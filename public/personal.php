<?php

namespace WEEEOpen\Crauto;

use League\Plates\Engine as Plates;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();

$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN, CRAUTO_LDAP_GROUPS_DN, false);
$info = $ldap->getInfo($_SESSION['uid']);

$templates = new Plates('..' . DIRECTORY_SEPARATOR . 'templates');
echo $templates->render('user', [
	'uid' => $_SESSION['uid'],
	'name' => $_SESSION['cn'],
	'attributes' => $info
]);
