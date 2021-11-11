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

	public static function shortListEntry(string $uid, string $cn, ?string $schacpersonaluniquecode): string {
		$schacpersonaluniquecode = $schacpersonaluniquecode ?? 'no matricola';
		return /** @lang HTML */ "<a href=\"/people.php?uid=$uid\">$cn</a>, $schacpersonaluniquecode <a class=\"btn btn-sm btn-outline-dark m-1 p-1\" href=\"/sir.php?uid=$uid\"><i class=\"fa fa-download mr-1\"></i>Get SIR</a>";
	}
}
