<?php


namespace WEEEOpen\Crauto;

/* Yes, I know, a class with 2 static methods shouldn't be a class...
But at least it's getting autoloaded and is tidier than a giant functions.php. */

use DateTime;

class Validation {
	const allowedAttributesUser = [
		'uid',
		'cn',
		'givenname',
		'sn',
		'memberof',
		'mail',
		'schacpersonaluniquecode',
		'degreecourse',
		'schacdateofbirth',
		'schacplaceofbirth',
		'mobile',
		'safetytestdate',
		'telegramid',
		'telegramnickname',
		'sshpublickey',
	];
	const allowedAttributesAdmin = [
		'uid',
		'cn',
		'givenname',
		'sn',
		'memberof',
		'mail',
		'schacpersonaluniquecode',
		'degreecourse',
		'schacdateofbirth',
		'schacplaceofbirth',
		'mobile',
		'safetytestdate',
		'telegramid',
		'telegramnickname',
		'sshpublickey',
		'description',
	];
	const editableAttributesUser = [
		'mail',
		'schacpersonaluniquecode',
		'degreecourse',
		'telegramid',
		'telegramnickname',
	];
	const editableAttributesAdmin = [
		'cn',
		'givenname',
		'sn',
		'memberof',
		'mail',
		'schacpersonaluniquecode',
		'degreecourse',
		'schacdateofbirth',
		'schacplaceofbirth',
		'mobile',
		'safetytestdate',
		'telegramid',
		'telegramnickname',
		'description',
	];

	public static function normalize(Ldap $ldap, array $inputs): array {
		foreach($inputs as $k => $v) {
			$inputs[$k] = trim($v);
		}
		if(self::hasValue('telegramid', $inputs)) {
			$inputs['telegramid'] = (string) ((int) $inputs['telegramid']);
		}
		if(self::hasValue('schacpersonaluniquecode', $inputs)) {
			$id = $inputs['schacpersonaluniquecode'];
			$letter = substr($id, 0, 1);
			$letter = strtolower($letter);
			if(ctype_digit($letter)) {
				$numbers = $id;
				$letter = 's';
			} else {
				$numbers = substr($id, 1);
			}
			$inputs['schacpersonaluniquecode'] = $letter . $numbers;
		}
		if(self::hasValue('schacdateofbirth', $inputs)) {
			$inputs['schacdateofbirth'] = self::dateHtmlToSchac($inputs['schacdateofbirth']);
		}
		if(self::hasValue('safetytestdate', $inputs)) {
			$inputs['safetytestdate'] = self::dateHtmlToSchac($inputs['safetytestdate']);
		}
		if(self::hasValue('mobile', $inputs)) {
			$inputs['mobile'] = self::mobile($inputs['mobile']);
		}
		if(self::hasValue('memberof', $inputs)) {
			try {
				$groupNames = explode("\r\n", $inputs['memberof']);
				$groupNames = array_filter($groupNames, function($name) { return $name !== ''; });
				$inputs['memberof'] = $ldap->groupNamesToDn($groupNames);
			} catch(LdapException $e) {
				if($e->getCode() === 1) {
					throw new ValidationException($e->getMessage(), 0, $e);
				} else {
					throw $e;
				}
			}
		}

		// Arrays are passed by value and copy-on-write, $inputs is now a copy of the array outside this function
		return $inputs;
	}

	public static function validate(array $inputs) {
		foreach($inputs as $attr => $input) {
			$strlen = mb_strlen($input);
			if($attr === 'description') {
				if($strlen > 10000) {
					throw new ValidationException("Notes too long: $strlen characters, limit is 10000");
				}
			}
			if($strlen > 500) {
				throw new ValidationException("$attr too long: $strlen characters, limit is 500");
			}
		}
		if(self::hasValue('mail', $inputs)) {
			if(
				strlen($inputs['mail'])  >= 3 &&
				substr($inputs['mail'], 0, 1) !== '@' &&
				substr($inputs['mail'], -1, 1) !== '@' &&
				substr_count($inputs['mail'], '@') >= 1
			) {
				// Email should be:
				// At least 3 characters long (a@b)
				// Not start with an @
				// Not end with an @
				// Contain at least one @ somewhere ("@"@example.com should be valid, I think)
			} else {
				throw new ValidationException('Invalid email address');
			}
		}
		if(self::hasValue('schacpersonaluniquecode', $inputs)) {
			if(strlen($inputs['schacpersonaluniquecode']) < 2) {
				throw new ValidationException('ID number too short (just 1 character?)');
			}
			$letter = substr($inputs['schacpersonaluniquecode'], 0, 1);
			$numbers = substr($inputs['schacpersonaluniquecode'], 1);
			if($letter !== 's' && $letter !== 'd') {
				throw new ValidationException('ID number should begin with "s" or "d"');
			}
			if(!ctype_digit($numbers)) {
				throw new ValidationException('ID number should begin with "s" or "d", followed by numbers');
			}
		}
		if(self::hasValue('schacplaceofbirth', $inputs)) {
			if(preg_match('#^\w[\w\s]*(\([A-Za-z][A-Za-z]\))?, \w[\w\s]*$#', $inputs['schacplaceofbirth']) !== 1) {
				throw new ValidationException('Place of birth does not match regex');
			}
		}
		if(self::hasValue('uid', $inputs)) {
			if(preg_match('#^[a-zA-Z][a-zA-Z0-9-_\.]*$#', $inputs['uid']) !== 1) {
				throw new ValidationException('Username should contain only alphanumeric characters, dash, underscore and dot (A-Z a-z 0-9 - _ .), and begin with a letter');
			}
			if(strlen($inputs['uid']) > 50) {
				throw new ValidationException('Username too long, maximum is 50 characters');
			}
		}
		if(self::hasValue('mobile', $inputs)) {
			if(strlen($inputs['mobile']) < 2) {
				throw new ValidationException('Cellphone number too short (1 digit?)');
			}
			if($inputs['mobile']{0} === '+') {
				$num = substr($inputs['mobile'], 1);
			} else {
				$num = $inputs['mobile'];
			}
			if(!ctype_digit($num)) {
				throw new ValidationException('Cellphone number should contain only digits and optionally a +, sorry if you have a number with an extension or some weird symbols in it');
			}
		}
	}

	public static function dateSchacToHtml(string $date): string {
		return DateTime::createFromFormat('Ymd', $date)->format('Y-m-d');
	}

	public static function dateHtmlToSchac(string $date): string {
		return DateTime::createFromFormat('Y-m-d', $date)->format('Ymd');
	}

	private static function mobile(string $mobile): string {
		$plus = substr($mobile, 0, 1) === '+' ? '+' : '';
		$mobile = preg_replace('/[^0-9]/', '', $mobile);
		return $plus . $mobile;
	}

	private static function hasValue(string $attr, array $attrs): bool {
		return isset($attrs[$attr]) && $attrs[$attr] !== '' && $attrs[$attr] !== null;
	}
}
