<?php

namespace WEEEOpen\Crauto;

use DateTimeZone;
use InvalidArgumentException;

require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
Authentication::requireLogin();
if (!Authentication::isAdmin()) {
	$template = Template::create();
	echo $template->render('403');
	exit;
}

if (isset($_GET['uid'])) {
	$allowedAttributes = Validation::ALLOWED_ATTRIBUTES_ADMIN;
	$editableAttributes = array_combine(Validation::EDITABLE_ATTRIBUTES_ADMIN, Validation::EDITABLE_ATTRIBUTES_ADMIN);

	$targetUid = $_GET['uid'];

	$attributes = [];
	$allGroups = [];
	$error = null;
	try {
		$ldap = new Ldap(
			CRAUTO_LDAP_URL,
			CRAUTO_LDAP_BIND_DN,
			CRAUTO_LDAP_PASSWORD,
			CRAUTO_LDAP_USERS_DN,
			CRAUTO_LDAP_GROUPS_DN,
			CRAUTO_LDAP_STARTTLS
		);
		$attributes = $ldap->getUser($targetUid, array_merge($allowedAttributes, ['createtimestamp', 'modifytimestamp']));
		$targetUid = $attributes['uid'] ?? $targetUid; // Canonicalize uid, or use the supplied one
		// Do not move elsewhere, otherwise you get empty groups in case of errors above here
		$allGroups = $ldap->getGroups();

		// Cannot change its own password without entering the old password. Can change any other password without knowning
		// the old one, but at least it's a thin veil of protection (would allow to bypass the authentication.php
		// password change otherwise)
		// The strtolower stuff is an additional safeguard but the the uid canonicalization above should make it kind of
		// useless...
		$requireOldPasswordForChange = strtolower($_SESSION['uid']) === strtolower($attributes['uid']);

		if (isset($_POST) && !empty($_POST)) {
			if (isset($_POST['password1'])) {
				Validation::handlePasswordChangePost($ldap, $targetUid, $_POST, $requireOldPasswordForChange);
			} else {
				Validation::handleUserEditPost($editableAttributes, $ldap, $targetUid, $attributes);
			}
			http_response_code(303);
			// $_SERVER['REQUEST_URI'] is already url encoded
			header("Location: {$_SERVER['REQUEST_URI']}");
			exit;
		}
	} catch (LdapException | ValidationException | InvalidArgumentException $e) {
		$error = $e->getMessage();
	}

	$groups = [];
	foreach ($attributes['memberof'] as $dn) {
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
	$website = isset($_GET['for']) && $_GET['for'] == 'website';
	try {
		$ldap = new Ldap(CRAUTO_LDAP_URL, CRAUTO_LDAP_BIND_DN, CRAUTO_LDAP_PASSWORD, CRAUTO_LDAP_USERS_DN, CRAUTO_LDAP_GROUPS_DN, CRAUTO_LDAP_STARTTLS);
		$users = $ldap->getUsersList(new DateTimeZone('Europe/Rome'), $website ? ['degreecourse', 'websitedescription'] : ['websitedescription']);
	} catch (LdapException $e) {
		$error = $e->getMessage();
	}

	$template = Template::create();
	$template->addData(['currentSection' => 'people'], 'navbar');

	if ($website) {
		$excludedGroups = explode(',', CRAUTO_WEBSITE_IGNORE_GROUPS);
		$excludedGroups = array_combine($excludedGroups, $excludedGroups);
		if (count($excludedGroups) > 0) {
			$usersFiltered = [];
			foreach ($users as $user) {
				$groups = $user['memberof'] ?? [];
				$exclude = false;
				foreach ($groups as $group) {
					if (array_key_exists($group, $excludedGroups)) {
						$exclude = true;
						break;
					}
				}
				if (!$exclude) {
					$usersFiltered[] = $user;
				}
			}
		} else {
			$usersFiltered = $users;
		}

		echo $template->render('websiteuserlist', [
			'users' => $usersFiltered,
			'excludedGroups' => $excludedGroups,
			'error' => $error,
		]);
	} else {
		echo $template->render('userlist', [
			'users' => $users,
			'error' => $error,
		]);
	}
}
