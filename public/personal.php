<?php

namespace WEEEOpen\Crauto;

use InvalidArgumentException;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();

$allowedAttributes = Validation::allowedAttributesUser;
if(Authentication::isAdmin()) {
	$editableAttributes = array_combine(Validation::editableAttributesAdmin, Validation::editableAttributesAdmin);
	// Some attributes are editable, but only on the "people" page, not on the personal page, where they aren't even shown...
	$editableAttributes = array_intersect($editableAttributes, $allowedAttributes);
} else {
	$editableAttributes = array_combine(Validation::editableAttributesUser, Validation::editableAttributesUser);
}

$attributes = [];
$error = null;
try {
	$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN,
		CRAUTO_LDAP_GROUPS_DN, false);
	$attributes = $ldap->getUser($_SESSION['uid'], $allowedAttributes);

	if(isset($_GET['download'])) {
		header('Content-Type: application/json');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Description: File Transfer');
		header("Content-Disposition: attachment; filename=${_SESSION['uid']}.json");
		echo json_encode($attributes, JSON_PRETTY_PRINT);
		exit;
	}

	if(isset($_POST) && !empty($_POST)) {
		Validation::handleUserEditPost($editableAttributes, $ldap, $_SESSION['uid'], $attributes);
		http_response_code(303);
		header('Location: personal.php');
		exit;
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
echo $template->render('personaleditor', [
	'error' => $error,
	'attributes' => $attributes,
	'allowedAttributes' => $allowedAttributes,
	'editableAttributes' => $editableAttributes,
]);
