<?php


namespace WEEEOpen\Crauto;

use League\Plates\Engine as Plates;

class Template {
	public static function create(): Plates {
		$engine = new Plates('..' . DIRECTORY_SEPARATOR . 'templates');
		$engine->addData(['isAdmin' => Authentication::splitGroups($_SESSION['groups'])], 'navbar');
		return $engine;
	}
}