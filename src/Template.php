<?php


namespace WEEEOpen\Crauto;

use League\Plates\Engine as Plates;

class Template {
	public static function create(): Plates {
		$loggedIn = Authentication::isLoggedIn();
		$engine = new Plates('..' . DIRECTORY_SEPARATOR . 'templates');
		$engine->addData([
			'authenticated' => $loggedIn,
			'isAdmin' => $loggedIn && self::isAdmin(),
		], 'navbar');
		return $engine;
	}

	private static function isAdmin() {
		$groups = Authentication::splitGroups($_SESSION['groups']);
		return isset($groups['HR']);
	}
}