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
		'weeelabnickname',
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
		'weeelabnickname',
		'websitedescription',
		'description',
		'nsaccountlock',
		'haskey',
		'signedsir',
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
		'weeelabnickname',
		'websitedescription',
		'description',
		'nsaccountlock',
		'haskey',
		'signedsir',
	];
	const allowedAttributesRegister = [
		'uid',
		'userpassword',
		'telegramid',
		'telegramnickname',
		'cn',
		'givenname',
		'sn',
		'mail',
		'mobile',
		'degreecourse',
		'schacpersonaluniquecode',
		'schacdateofbirth',
		'schacplaceofbirth',
	];

	protected static function normalize(Ldap $ldap, array $inputs): array {
		foreach($inputs as $k => $v) {
			$inputs[$k] = trim($v);
			if($v === '') {
				$inputs[$k] = null;
			}
		}
		if(self::hasValue('telegramid', $inputs)) {
			$inputs['telegramid'] = (string) ((int) $inputs['telegramid']);
		}
		if(self::hasValue('nsaccountlock', $inputs)) {
			$inputs['nsaccountlock'] = boolval($inputs['nsaccountlock']) ? 'true' : '';
		}
		if(self::hasValue('haskey', $inputs)) {
			$inputs['haskey'] = boolval($inputs['haskey']) ? 'true' : '';
		}
		if(self::hasValue('signedsir', $inputs)) {
			$inputs['signedsir'] = boolval($inputs['signedsir']) ? 'true' : '';
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
			if(preg_match('#^\d{4}-\d{2}-\d{2}$#u', $inputs['schacdateofbirth']) !== 1) {
				throw new ValidationException('Date of birth should be in YYYY-MM-DD format (or whatever the date picker chooses)');
			}
		}
		if(self::hasValue('safetytestdate', $inputs)) {
			if(preg_match('#^\d{4}-\d{2}-\d{2}$#u', $inputs['safetytestdate']) !== 1) {
				throw new ValidationException('Date of the test on safety should be in YYYY-MM-DD format (or whatever the date picker chooses)');
			}
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
		if(self::hasValue('sshpublickey', $inputs)) {
			$inputs['sshpublickey'] = explode("\r\n", $inputs['sshpublickey']);
			$inputs['sshpublickey'] = array_filter($inputs['sshpublickey'], function($name) { return $name !== ''; });
		}
		if(self::hasValue('weeelabnickname', $inputs)) {
			$inputs['weeelabnickname'] = explode("\r\n", $inputs['weeelabnickname']);
			$inputs['weeelabnickname'] = array_filter($inputs['weeelabnickname'], function($name) { return $name !== ''; });
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
		if(array_key_exists('sshpublickey', $inputs) && !is_array($inputs['sshpublickey'])) {
			$inputs['sshpublickey'] = [];
		}
		if(array_key_exists('weeelabnickname', $inputs) && !is_array($inputs['weeelabnickname'])) {
			$inputs['weeelabnickname'] = [];
		}
		if(array_key_exists('memberof', $inputs) && !is_array($inputs['memberof'])) {
			$inputs['memberof'] = [];
		}

		// Arrays are passed by value and copy-on-write, $inputs is now a copy of the array outside this function
		return $inputs;
	}

	protected static function validate(array $inputs) {
		foreach($inputs as $attr => $input) {
			if(is_array($input)) {
				continue;
			}
			$strlen = mb_strlen($input);
			if($attr === 'description') {
				if($strlen > 10000) {
					throw new ValidationException("Notes too long: $strlen characters, limit is 10000");
				}
			} elseif($attr === 'sshpublickey') {
				if($strlen > 10000) {
					throw new ValidationException("SSH public keys too long: $strlen characters, limit is 10000");
				}
			} elseif($strlen > 500) {
				throw new ValidationException("$attr too long: $strlen characters, limit is 500");
			}
		}
		if(self::hasValue('mail', $inputs)) {
			// Email should be:
			// At least 3 characters long (a@b)
			// Not start with an @
			// Not end with an @
			// Contain at least one @ somewhere ("@"@example.com should be valid, I think)
			if(
				strlen($inputs['mail']) < 3 ||
				substr($inputs['mail'], 0, 1) === '@' ||
				substr($inputs['mail'], -1, 1) === '@' ||
				substr_count($inputs['mail'], '@') < 1
			) {
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
			if(preg_match('#^\w[\w\s]*(?:\([A-Za-z][A-Za-z]\))?, .+$#u', $inputs['schacplaceofbirth']) !== 1) {
				throw new ValidationException('Place of birth does not match regex');
			}
		}
		if(self::hasValue('schacdateofbirth', $inputs)) {
			if(strlen($inputs['schacdateofbirth']) !== 8 || !is_numeric($inputs['schacdateofbirth'])) {
				throw new ValidationException('Date of birth cannot be converted to SCHAC format');
			}
		}
		if(self::hasValue('safetytestdate', $inputs)) {
			if(strlen($inputs['safetytestdate']) !== 8 || !is_numeric($inputs['safetytestdate'])) {
				throw new ValidationException('Date of the test on safety cannot be converted to SCHAC format');
			}
		}
		if(self::hasValue('nsaccountlock', $inputs)) {
			if($inputs['nsaccountlock'] !== 'true') {
				throw new ValidationException('nsAccountLock can only be true or be removed');
			}
		}
		if(self::hasValue('haskey', $inputs)) {
			if($inputs['haskey'] !== 'true') {
				throw new ValidationException('hasKey can only be true or be removed');
			}
		}
		if(self::hasValue('signedsir', $inputs)) {
			if($inputs['signedsir'] !== 'true') {
				throw new ValidationException('signedSir can only be true or be removed');
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
			if($inputs['mobile'][0] === '+') {
				$num = substr($inputs['mobile'], 1);
			} else {
				$num = $inputs['mobile'];
			}
			if(!ctype_digit($num)) {
				throw new ValidationException('Cellphone number should contain only digits and optionally a +, sorry if you have a number with an extension or some weird symbols in it');
			}
		}
	}

	/**
	 * What it says on the tin.
	 * Throws a fatal error if not in the correct input format, check before calling this function...
	 *
	 * @param string $date Ymd from LDAP
	 *
	 * @return string Y-m-d for HTML input tags
	 */
	public static function dateSchacToHtml(string $date): string {
		return DateTime::createFromFormat('Ymd', $date)->format('Y-m-d');
	}

	/**
	 * What it says on the tin.
	 * Throws a fatal error if not in the correct input format, check before calling this function...
	 *
	 * @param string $date Y-m-d from HTML input tags
	 *
	 * @return string Ymd for LDAP
	 */
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

	/**
	 * Handle POST of data from an edit user form
	 *
	 * @param array $editableAttributes
	 * @param Ldap $ldap
	 * @param string $uid UID to update
	 * @param array|null $previous attributes
	 */
	public static function handleUserEditPost(array $editableAttributes, Ldap $ldap, string $uid, ?array $previous): void {
		$edited = array_intersect_key($_POST, $editableAttributes);
		if(isset($editableAttributes['nsaccountlock']) && !isset($edited['nsaccountlock'])) {
			$edited['nsaccountlock'] = '';
		}
		if(isset($editableAttributes['haskey']) && !isset($edited['haskey'])) {
			$edited['haskey'] = '';
		}
		if(isset($editableAttributes['signedsir']) && !isset($edited['signedsir'])) {
			$edited['signedsir'] = '';
		}
		if(isset($edited['memberof'])) {
			// Backwards compatibility layer
			$edited['memberof'] = implode("\r\n", $edited['memberof']);
		}
		$edited = Validation::normalize($ldap, $edited);
		Validation::validate($edited);
		$ldap->updateUser($uid, $edited, $previous);
	}

	/**
	 * Handle POST of data from a registration form
	 *
	 * @param array $attributes Attributes from POST request
	 * @param array $allowedAttributes
	 * @param Ldap $ldap
	 * @param string[] $degreeCourses
	 * @param string[] $countries
	 * @param string[] $province
	 */
	public static function handleUserRegisterPost(array $attributes, array $allowedAttributes, Ldap $ldap, array $degreeCourses, array $countries, array $province): void {
		if(!self::hasValue('register-tos', $attributes) || $attributes['register-tos'] !== 'on') {
			throw new ValidationException('Accept our privacy policy to continue');
		}

		// Check degree course according to the list
		if(!self::hasValue('degreecourse', $attributes)) {
			throw new ValidationException('Select a degree course');
		}
		// Key and value are the same, no need to get the value
		if(!isset($degreeCourses[$attributes['degreecourse']])) {
			throw new ValidationException('Invalid degree course, select one from the list');
		}

		if(!self::hasValue('givenname', $attributes) || !self::hasValue('sn', $attributes)) {
			// This is checked again later, but oh well...
			throw new ValidationException('Name and surname are mandatory');
		}

		// Normalize!
		$attributes['givenname'] = trim($attributes['givenname']);
		$attributes['sn'] = trim($attributes['sn']);

		// This may become longer than 500 characters and fail validation later. Well... too bad.
		$attributes['cn'] = $attributes['givenname'] . ' ' . $attributes['sn'];

		if(!self::hasValue('password1', $attributes) || !self::hasValue('password2', $attributes)) {
			throw new ValidationException('Missing password or password confirmation');
		}
		if($attributes['password1'] !== $attributes['password2']) {
			throw new ValidationException('Password and password confirmation do not match');
		}
		$password = $attributes['userpassword'] = $attributes['password1'];

		$edited = array_intersect_key($attributes, array_combine($allowedAttributes, $allowedAttributes));
		unset($attributes);

		$edited = Validation::normalize($ldap, $edited); // This will trim the password. This needs to be undone...
		$edited['userpassword'] = $password; // ...done. Or undone, as you prefer.

		// Trimming may have eliminated some fields, let's see...

		if(!self::hasValue('uid', $edited)) {
			throw new ValidationException('Username is mandatory');
		}

		// Password already validated

		if(!self::hasValue('givenname', $edited)) {
			throw new ValidationException('Name is mandatory');
		}

		if(!self::hasValue('sn', $edited)) {
			throw new ValidationException('Surname is mandatory');
		}

		if(!self::hasValue('degreecourse', $edited)) {
			throw new ValidationException('Degree course is mandatory');
		}

		if(!self::hasValue('schacpersonaluniquecode', $edited)) {
			throw new ValidationException('Student ID is mandatory');
		}

		// Ok, now validate the structure of fields where that's possible
		Validation::validate($edited);

		// Oh, one more thing...
		$edited['nsAccountLock'] = 'true';

		$edited['memberOf'] = $ldap->groupNamesToDn(explode(',', CRAUTO_DEFAULT_GROUPS));

		if($edited['mail'] === null) {
			unset($edited['mail']);
		}

		if($edited['schacdateofbirth'] === null) {
			unset($edited['schacdateofbirth']);
		}

		if($edited['telegramid'] === null) {
			unset($edited['telegramid']);
		}

		if($edited['telegramnickname'] === null) {
			unset($edited['telegramnickname']);
		}

		$ldap->addUser($edited);
	}

	/**
	 * Handle POST of data from an edit user form
	 *
	 * @param Ldap $ldap
	 * @param string $uid UID to update
	 * @param array $form form values
	 * @param bool $requireOldPassword
	 */
	public static function handlePasswordChangePost(Ldap $ldap, string $uid, array $form, bool $requireOldPassword = true): void {
		$required = ['password1', 'password2'];
		if($requireOldPassword) {
			$required[] = 'oldpassword';
		}
		$form = array_intersect_key($form, array_combine($required, $required));
		if(count($form) < count($required)) {
			throw new ValidationException('Provide all the required passwords');
		}
		if($form['password1'] !== $form['password2']) {
			throw new ValidationException('Password does not match confirmation password');
		}

		$dn = $ldap->getUserDn($uid);

		if($requireOldPassword) {
			try {
				// If this doesn't throw any exception, we're good to go
				new Ldap($ldap->getUrl(), $dn, $form['oldpassword'], '', '', $ldap->getStarttls());
			} catch(LdapException $e) {
				if($e->getMessage() === 'Bind with LDAP server failed') {
					throw new LdapException('Current password is incorrect (' . $e->getMessage() . ')');
				}
				throw $e;
			}
		}

		$ldap->updatePassword($dn, $form['password1']);
	}
}
