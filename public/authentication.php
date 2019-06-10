<?php

namespace WEEEOpen\Crauto;

use InvalidArgumentException;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();

$error = null;
try {
	$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN,
		CRAUTO_LDAP_GROUPS_DN, false);

	if(isset($_POST) && !empty($_POST)) {
		Validation::handlePasswordChangePost($ldap, $_SESSION['uid'], $_POST);
		$location = 'authentication.php';
		$_SESSION['success'] = 'Password updated successfully';
		http_response_code(303);
		header("Location: $location");
		exit;
	}
} catch(LdapException $e) {
	$error = $e->getMessage();
} catch(InvalidArgumentException $e) {
	$error = $e->getMessage();
} catch(ValidationException $e) {
	$error = $e->getMessage();
}

if(isset($_SESSION['success'])) {
	$success = $_SESSION['success'];
	unset($_SESSION['success']);
} else {
	$success = null;
}

$template = Template::create();
$template->addData(['currentSection' => 'authentication'], 'navbar');
echo $template->render('authenticationeditor', [
	'error' => $error,
	'success' => $success,
	'target' => '/authentication.php'
]);
