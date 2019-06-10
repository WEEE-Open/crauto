<?php

namespace WEEEOpen\Crauto;

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
	$error = null;
	try {
		$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN,
			CRAUTO_LDAP_GROUPS_DN, false);
		$attributes = $ldap->getUser($targetUid, $allowedAttributes);

		if(isset($_POST) && !empty($_POST)) {
			// $_SERVER['REQUEST_URI'] is already url encoded
			Validation::handlePost($editableAttributes, $ldap, $targetUid, $attributes, $_SERVER['REQUEST_URI']);
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
	$attributes['safetytestdate'] = isset($attributes['safetytestdate']) ? Validation::dateSchacToHtml($attributes['safetytestdate']) : '';
	$attributes['schacdateofbirth'] = isset($attributes['schacdateofbirth']) ? Validation::dateSchacToHtml($attributes['schacdateofbirth']) : '';

	$template = Template::create();
	$template->addData(['currentSection' => 'people'], 'navbar');
	$template->addData(['title' => "Edit $targetUid"]);
	echo $template->render('usereditor', [
		'error' => $error,
		'attributes' => $attributes,
		'allowedAttributes' => $allowedAttributes,
		'editableAttributes' => $editableAttributes,
	]);
} else {
	$users = [];
	$error = null;
	try {
		$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN,
			CRAUTO_LDAP_GROUPS_DN, false);
		$users = $ldap->getUsers(['uid', 'cn', 'memberof', 'nsaccountlock']);

		foreach($users as &$user) {
			if(isset($user['memberof'])) {
				$groups = [];
				foreach($user['memberof'] as $dn) {
					$groups[] = Ldap::groupDnToName($dn);
				}
				$user['memberof'] = $groups;
			}
		}
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


