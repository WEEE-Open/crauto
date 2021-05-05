<?php


namespace WEEEOpen\Crauto;

use League\Plates\Engine as Plates;

class Template {
	public static function create(): Plates {
		$loggedIn = Authentication::isLoggedIn();
		$engine = new Plates('..' . DIRECTORY_SEPARATOR . 'templates');
		$engine->addData([
			'authenticated' => $loggedIn,
			'isAdmin' => $loggedIn && Authentication::isAdmin(),
		], 'navbar');
		return $engine;
	}

	public static function telegramColumn($nickname, $id): string {
		if(isset($nickname)) {
			return '<a href="https://t.me/' . $nickname . '">@' . $nickname;
		} elseif(isset($id)) {
			return 'ID Only';
		} else {
			return '<span class="text-danger">N/A</span>';
		}
	}
}
