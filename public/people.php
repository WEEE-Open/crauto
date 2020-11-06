<?php

namespace WEEEOpen\Crauto;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();
if(!Authentication::isAdmin()) {
	$template = Template::create();
	echo $template->render('403');
	exit;
}

if(isset($_GET['uid'])) {
	$allowedAttributes = Validation::allowedAttributesAdmin;
	$editableAttributes = array_combine(Validation::editableAttributesAdmin, Validation::editableAttributesAdmin);

	$targetUid = $_GET['uid'];

	$attributes = [];
	$allGroups = [];
	$error = null;
	try {
		$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN,
			CRAUTO_LDAP_GROUPS_DN, CRAUTO_LDAP_STARTTLS);
		$attributes = $ldap->getUser($targetUid, array_merge($allowedAttributes, ['createtimestamp', 'modifytimestamp']));
		$targetUid = $attributes['uid'] ?? $targetUid; // Canonicalize uid, or use the supplied one
        $allGroups = $ldap->getGroups();

		// Cannot change its own password without entering the old password. Can change any other password without knowning
		// the old one, but at least it's a thin veil of protection (would allow to bypass the authentication.php
		// password change otherwise)
		// The strtolower stuff is an additional safeguard but the the uid canonicalization above should make it kind of
		// useless...
		$requireOldPasswordForChange = strtolower($_SESSION['uid']) === strtolower($attributes['uid']);

		if(isset($_POST) && !empty($_POST)) {
			if(isset($_POST['password1'])) {
				Validation::handlePasswordChangePost($ldap, $targetUid, $_POST, $requireOldPasswordForChange);
				http_response_code(303);
				header("Location: ${_SERVER['REQUEST_URI']}");
				exit;
			} else {
				Validation::handleUserEditPost($editableAttributes, $ldap, $targetUid, $attributes);
				http_response_code(303);
				// $_SERVER['REQUEST_URI'] is already url encoded
				header("Location: ${_SERVER['REQUEST_URI']}");
				exit;
			}
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
	$attributes['safetytestdate'] = isset($attributes['safetytestdate']) ? Validation::dateSchacToHtml($attributes['safetytestdate']) : '';
	$attributes['schacdateofbirth'] = isset($attributes['schacdateofbirth']) ? Validation::dateSchacToHtml($attributes['schacdateofbirth']) : '';

	$template = Template::create();
	$template->addData(['currentSection' => 'people'], 'navbar');
	$template->addData(['title' => "Edit $targetUid"]);
	echo $template->render('usereditor', [
		'error' => $error,
		'attributes' => $attributes,
		'editableAttributes' => $editableAttributes,
		'allowedAttributes' => $allowedAttributes,
		'adminRequireOldPassword' => $requireOldPasswordForChange ?? true,
        'allGroups' => $allGroups
    ]);
} else {
	$error = null;
	$users = [];
	try {
		$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN, CRAUTO_LDAP_GROUPS_DN, CRAUTO_LDAP_STARTTLS);
		$users = $ldap->getUsersList(new DateTimeZone('Europe/Rome'));
	} catch(LdapException $e) {
		$error = $e->getMessage();
	}

	$template = Template::create();
	$template->addData(['currentSection' => 'people'], 'navbar');
	echo $template->render('userlist', [
		'users' => $users,
		'error' => $error,
	]);
}


