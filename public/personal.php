<?php

namespace WEEEOpen\Crauto;

use InvalidArgumentException;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();

$allowedAttributes = Validation::allowedAttributesUser;
if(Authentication::isAdmin()) {
	$editableAttributes = array_combine(Validation::editableAttributesAdmin, Validation::editableAttributesAdmin);
} else {
	$editableAttributes = array_combine(Validation::editableAttributesUser, Validation::editableAttributesUser);
}

$attributes = [];
$error = null;
try {
	$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN,
		CRAUTO_LDAP_GROUPS_DN, false);
	$attributes = $ldap->getUser($_SESSION['uid'], $allowedAttributes);

	if(isset($_POST) && !empty($_POST)) {
		$edited = array_intersect_key($_POST, $editableAttributes);
		$edited = Validation::normalize($ldap, $edited);
		Validation::validate($edited);
		$ldap->updateUser($_SESSION['uid'], $edited, $attributes);
		http_response_code(303);
		header("Location: /personal.php");
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
$template->addData(['title' => "Personal profile"]);
echo $template->render('usereditor', [
	'error' => $error,
	'attributes' => $attributes,
	'allowedAttributes' => $allowedAttributes,
	'editableAttributes' => $editableAttributes,
]);
