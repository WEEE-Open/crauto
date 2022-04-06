<?php


namespace WEEEOpen\Crauto;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;

class Ldap {
	protected $ds;
	protected $groupsDn;
	protected $usersDn;
	protected $url;
	protected $starttls;
	public static $multivalued = ['memberof' => true, 'sshpublickey' => true, 'weeelabnickname' => true];
	private const EXAMPLE_USERS = [
		'test.administrator' => [
			'uid' => 'test.administrator',
			'cn' => 'Test Administrator',
			'sn' => 'Administrator',
			'memberof' => ["cn=Cloud,ou=Groups,dc=weeeopen,dc=it","cn=Admin,ou=Groups,dc=weeeopen,dc=it"] ,
			'createtimestamp' => '20191025105022Z',
			'modifytimestamp' => '20191025155317Z',
			'safetytestdate' => '20160909',
			'signedsir' => 'true',
			'haskey' => 'true',
			'schacpersonaluniquecode' => 's111111',
			'telegramid' => null,
			'telegramnickname' => null,
			'sshpublickey' => [],
			'weeelabnickname' => ['io'],
			'websitedescription' => "Il capo supremo\nSu due righe",
			'description' => '',
            'nsaccountlock' => null
        ],
        'alice' => [
            'uid' => 'alice',
            'cn' => 'Alice Test',
            'sn' => 'Test',
            'memberof' => ["cn=Cloud,ou=Groups,dc=weeeopen,dc=it", "cn=Gente,ou=Groups,dc=weeeopen,dc=it", "cn=Riparatori,ou=Groups,dc=weeeopen,dc=it"] ,
            'createtimestamp' => '20191025105022Z',
            'modifytimestamp' => '20191025155317Z',
            'safetytestdate' => '20991104',
            'signedsir' => null,
            'haskey' => null,
            'schacpersonaluniquecode' => 's22222',
            'telegramid' => null,
            'telegramnickname' => null,
            'sshpublickey' => [],
            'weeelabnickname' => [],
            'websitedescription' => 'Persona',
            'description' => '',
            'nsaccountlock' => 'true'
        ],
        'brodino' => [
            'uid' => 'brodino',
            'cn' => 'Bro Dino',
            'sn' => 'Dino',
            'memberof' => ["cn=Admin,ou=Groups,dc=weeeopen,dc=it", "cn=Gente,ou=Groups,dc=weeeopen,dc=it"],
            'createtimestamp' => '20191025105022Z',
            'modifytimestamp' => '20191025155317Z',
            'safetytestdate' => '20201104',
            'signedsir' => 'true',
            'haskey' => null,
            'nsaccountlock' => 'true',
            'telegramid' => '123456789',
            'schacpersonaluniquecode' => 's333333',
            'sshpublickey' => [],
            'weeelabnickname' => [],
	        'description' => '',
            'telegramnickname' => 'brodino'
        ],
		'bob' => [
			'uid' => 'bob',
			'cn' => 'Bob "Il grande testatore" Testington',
			'sn' => 'Testington',
			'memberof' => ["cn=Admin,ou=Groups,dc=weeeopen,dc=it"],
			'createtimestamp' => '20191216155022Z',
			'modifytimestamp' => '20191216155022Z',
			'safetytestdate' => '20201025',
			'signedsir' => null,
			'haskey' => null,
			'schacpersonaluniquecode' => 's55555',
			'degreecourse' => 'Ingegneria dell\'Ingegnerizzazione',
			'telegramid' => null,
			'telegramnickname' => null,
			'sshpublickey' => [],
			'weeelabnickname' => [],
			'description' => '',
			'nsaccountlock' => null
		],
        'broski' => [
            'uid' => 'broski',
            'cn' => 'Bro Ski',
            'sn' => 'Ski',
            'memberof' => ["cn=Admin,ou=Groups,dc=weeeopen,dc=it", "cn=Gente,ou=Groups,dc=weeeopen,dc=it"],
            'createtimestamp' => '20191025105022Z',
            'modifytimestamp' => '20191025155317Z',
            'safetytestdate' => '20201025',
            'signedsir' => null,
            'haskey' => null,
            'nsaccountlock' => null,
            'schacpersonaluniquecode' => 's4444444',
            'telegramnickname' => null,
            'sshpublickey' => [],
            'weeelabnickname' => [],
            'description' => '',
            'telegramid' => '123456789'
        ],
	];
	private const EXAMPLE_GROUPS = ['Admin', 'Persone', 'Cloud'];

	public function __construct(string $url, string $bindDn, string $password, string $usersDn, string $groupsDn, bool $startTls = true) {
		$this->url = $url;
		$this->starttls = $startTls;
		$this->groupsDn = $groupsDn;
		$this->usersDn = $usersDn;
		if(defined('TEST_MODE') && TEST_MODE) {
			error_log('TEST_MODE, not connecting to LDAP');
			return;
		}
		$this->ds = ldap_connect($url);
		if(!$this->ds) {
			throw new LdapException('Cannot connect to LDAP server');
		}
		if($startTls) {
			if(!ldap_start_tls($this->ds)) {
				throw new LdapException('Cannot STARTTLS with LDAP server');
			}
		}
		if(!ldap_bind($this->ds, $bindDn, $password)) {
			throw new LdapException('Bind with LDAP server failed');
		}
	}

	private static function usernameify(string $string): string {
		if(extension_loaded('iconv')) {
			$string = preg_replace("/[^\p{L}]/u", '', $string);
			$string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
		} else {
			$string = preg_replace("/[^A-Za-z]/", '', $string);
		}
		return strtolower($string);
	}

	public function getUser(string $uid, ?array $attributes = null): ?array {
		if(defined('TEST_MODE') && TEST_MODE) {
			error_log('TEST_MODE, returning sample user');
			return self::EXAMPLE_USERS[$uid];
		}
		$sr = $this->searchByUid($uid, $attributes);
		if($sr === null) {
			return null;
		}
		$user = ldap_get_entries($this->ds, $sr)[0];
		$user = self::simplify($user);
		if($attributes !== null) {
			$user = self::fillAndSortAttributes($user, $attributes);
		}
		return $user;
	}

	public function getInvitedUser(string $inviteCode, 	string $invitesDn): ?array {
		$sr = $this->searchByInvite($inviteCode, $invitesDn, ['givenname', 'sn', 'mail', 'schacpersonaluniquecode', 'degreecourse', 'telegramid', 'telegramnickname']);
		if($sr === null) {
			return null;
		}
		$attr = ldap_get_entries($this->ds, $sr)[0];
		$attr = self::simplify($attr);
		if(isset($attr['givenname']) && isset($attr['sn'])) {
			$attr['uid'] = self::usernameify($attr['givenname']) . '.' . self::usernameify($attr['sn']);
		}
		return $attr;
	}

    public function getGroups(): array {
        if(defined('TEST_MODE') && TEST_MODE) {
            error_log('TEST_MODE, returning sample groups');
            return self::EXAMPLE_GROUPS;
        } else {
        	$attributes = ['cn'];
	        $sr = ldap_search($this->ds, $this->groupsDn, '(cn=*)');
		    if(!$sr) {
			    throw new LdapException('Cannot search groups');
		    }

	        $simpler = $this->simplifyAll($sr, $attributes);

		    $groups = [];
	        foreach($simpler as $entry) {
		        if(isset($entry['cn'])) {
			        $groups[] = $entry['cn'];
		        }
	        }

	        return $groups;
        }
    }

	public function getUserDn(string $uid): string {
		$sr = $this->searchByUid($uid, ['entrydn']);
		if($sr === null) {
			throw new LdapException('Cannot find DN for user');
		}
		$theOnlyResult = ldap_first_entry($this->ds, $sr);
		return ldap_get_dn($this->ds, $theOnlyResult);
	}

	public function getUsers(array $attributes): array {
		if(defined('TEST_MODE') && TEST_MODE) {
			error_log('TEST_MODE, returning sample users');
			return array_values(self::EXAMPLE_USERS);
		}

		if(version_compare(PHP_VERSION, '7.3', '>=')) {
			// From PHP 7.3 onward, the serverctrls parameter is available. This is the only reasonable way to have the results sorted.
			// Well, if there's more than one page which is actually not supported, but let's use it anyway.
			$serverctrls = [
				[
					'oid' => LDAP_CONTROL_SORTREQUEST,
					// Should be true, should be supported out of the box on 389DS...
					// not even the examples in the manual managed to actually make it sort results, they're still "random".
					// Maybe some day in the future it will magically start to work and we can switch this to true, but that
					// day has yet to come.
					'iscritical' => false,
					'value' => [
						[
							'attr' => 'uid',
							'oid' => '2.5.13.3', // caseIgnoreOrderingMatch
						]
					]
				],
			];
			$sr = ldap_search($this->ds, $this->usersDn, '(uid=*)', $attributes, null, null, null, null, $serverctrls);
		} else {
			$sr = ldap_search($this->ds, $this->usersDn, '(uid=*)', $attributes);
		}
		if(!$sr) {
			throw new LdapException('Cannot search users');
		}

		$simpler = $this->simplifyAll($sr, $attributes);

		usort($simpler, function(array $a, array $b): int { return strcasecmp($a['uid'], $b['uid']); });
		return $simpler;
	}

	/**
	 * @param string $uid UID to search
	 * @param array|null $attributes Attributes to include in search result ("null" for all)
	 *
	 * @return resource|null $sr from ldap_search or none if no users are found
	 * @throws LdapException if cannot search or more than one user is found
	 */
	private function searchByUid(string $uid, ?array $attributes = null) {
		$uid = ldap_escape($uid, '', LDAP_ESCAPE_FILTER);
		$sr = ldap_search($this->ds, $this->usersDn, "(uid=$uid)", $attributes);
		if(!$sr) {
			throw new LdapException('Cannot search for uid');
		}
		$count = ldap_count_entries($this->ds, $sr);
		if($count === 0) {
			return null;
		} else if($count > 1) {
			throw new LdapException("$uid is not unique in $this->usersDn, $count results found");
		}

		return $sr;
	}

	private function searchByInvite(string $inviteCode, string $invitesDn, ?array $attributes = null) {
		$inviteCode = ldap_escape($inviteCode, '', LDAP_ESCAPE_FILTER);
		$sr = ldap_search($this->ds, $invitesDn, "(inviteCode=$inviteCode)", $attributes);
		if(!$sr) {
			throw new LdapException('Cannot search for invite code');
		}
		$count = ldap_count_entries($this->ds, $sr);
		if($count === 0) {
			return null;
		} else if($count > 1) {
			throw new LdapException("Duplicate invite code, $count results found");
		}

		return $sr;
	}

	public function updatePassword(string $dn, string $password) {
		$result = ldap_mod_replace($this->ds, $dn, ['userPassword' => $password]);
		if(!$result) {
			throw new LdapException('Cannot update password: ' . ldap_error($this->ds));
		}
	}

	public function updateUser(string $uid, array $replace, array $previous) {
		$dn = $this->getUserDn($uid);

		$modlist = [];
		foreach($replace as $attr => $values) {
			if($attr === 'memberof') {
				continue;
			}
			// Something changed
			if(!self::attrEquals($previous[$attr], $values)) {
				// Values needs to be an array for modlist
				if($values === null) {
					$values = [];
				} elseif(!is_array($values)) {
					$values = [$values];
				}
				if(count($values) > 0 && self::isEmpty($attr, $previous)) {
					// Does not exist: add
					$modlist[] = [
						"attrib"  => $attr,
						"modtype" => LDAP_MODIFY_BATCH_ADD,
						"values"  => $values
					];
				} elseif(count($values) <= 0) {
					// Actually delete (had a value, now has none)
					$modlist[] = [
						"attrib"  => $attr,
						"modtype" => LDAP_MODIFY_BATCH_REMOVE_ALL
					];
				} else {
					// Attribute already exists: replace
					$modlist[] = [
						"attrib"  => $attr,
						"modtype" => LDAP_MODIFY_BATCH_REPLACE,
						"values"  => $values
					];
				}
			}
		}
		if(isset($replace['memberof'])) {
			$replace['memberof'] = $replace['memberof'] ?? [];
			$previousMembership = $previous['memberof'] ?? [];
			$removedGroups = array_diff($previousMembership, $replace['memberof']);
			$addedGroups = array_diff($replace['memberof'], $previousMembership);
			$entry = [
				'member' => $dn,
			];
			foreach($addedGroups as $group) {
				$result = ldap_mod_add($this->ds, $group, $entry);
				if(!$result) {
					throw new LdapException("Cannot add $dn to $group");
				}
			}
			foreach($removedGroups as $group) {
				$result = ldap_mod_del($this->ds, $group, $entry);
				if(!$result) {
					throw new LdapException("Cannot remove $dn from $group");
				}
			}
		}
		if(!empty($modlist)) {
			$result = ldap_modify_batch($this->ds, $dn, $modlist);
			if($result === false) {
				throw new LdapException('Modification failed (' . ldap_error($this->ds) . ')');
			}
		}
	}

	public function addUser(array $edited) {
		$edited['objectClass'] = [
			'schacLinkageIdentifiers',
			'schacPersonalCharacteristics',
			'telegramAccount',
			'weeeOpenPerson',
			'nsMemberOf',
		];
		$dn = 'uid=' . ldap_escape($edited['uid'], '', LDAP_ESCAPE_DN) . ',' . $this->usersDn;
		$result = ldap_add($this->ds, $dn, $edited);
		if($result === false) {
			throw new LdapException('User add failed (' . ldap_error($this->ds) . ')');
		}
	}

	public function getUsersList(DateTimeZone $tz, array $moreAttrs = []): array {
		$users = $this->getUsers(array_merge([
			'uid',
			'cn',
			'sn',
			'schacpersonaluniquecode',
			'memberof',
			'nsaccountlock',
			'haskey',
			'signedsir',
			'safetytestdate',
			'telegramid',
			'telegramnickname',
			'createtimestamp',
		], $moreAttrs));

		foreach($users as &$user) {
			if(isset($user['memberof'])) {
				$groups = [];
				foreach($user['memberof'] as $dn) {
					$groups[] = Ldap::groupDnToName($dn);
				}
				$user['memberof'] = $groups;
			}
			if(isset($user['safetytestdate'])) {
				$user['safetytestdate'] = DateTime::createFromFormat('Ymd', $user['safetytestdate'], $tz);
			}
		}

		return $users;
	}

	public function deleteInvite(string $invitesDn, string $inviteCode) {
		$sr = $this->searchByInvite($inviteCode, $invitesDn, ['entrydn']);
		if($sr !== null) {
			$theOnlyResult = ldap_first_entry($this->ds, $sr);
			$dn = ldap_get_dn($this->ds, $theOnlyResult);
			ldap_delete($this->ds, $dn);
			// TODO: check result, if DN does not exist then it is deleted
			if(!$dn) {
				throw new LdapException('Cannot delete invite');
			}
		}
	}

	protected static function simplify(array $result): array {
		$things = [];
		foreach($result as $k => $v) {
			// dn seems to be always null!?
			if(!is_int($k) && $k !== 'count' && $k !== 'dn') {
				$attr = strtolower($k); // Should be already done, but repeat it anyway
				if($v['count'] === 1 && !isset(self::$multivalued[$k])) {
					$things[$attr] = $v[0];
				} else {
					$things[$attr] = array_diff_key($v, ['count' => true]);
				}
			}
		}
		return $things;
	}

	protected static function fillAndSortAttributes(array $result, array $attributes): array {
		$sorted = [];
		foreach($attributes as $attribute) {
			if(isset($result[$attribute])) {
				$sorted[$attribute] = $result[$attribute];
			} else {
				if(isset(self::$multivalued[$attribute])) {
					$sorted[$attribute] = [];
				} else {
					$sorted[$attribute] = null;
				}
			}
		}
		return $sorted;
	}

	public static function groupDnToName(string $dn): string {
		$pieces = ldap_explode_dn($dn, 1);
		if($pieces['count'] === 4 && strtolower($pieces[1]) === 'groups') {
			return $pieces[0];
		}
		throw new InvalidArgumentException("$dn is not a group DN");
	}

	public function groupNamesToDn(array $names): array {
		if(count($names) <= 0) {
			return [];
		}
		$escaped = [];
		$results = [];
		foreach($names as $name) {
			$escaped[] = 'cn=' . ldap_escape($name, '', LDAP_ESCAPE_FILTER);
			$results[strtolower($name)] = null;
		}
		$filter = '(|(' . implode(')(', $escaped) . '))';
		$sr = ldap_search($this->ds, $this->groupsDn, $filter, ['dn']);
		if(!$sr) {
			throw new LdapException('Cannot search groups');
		}

		$entry = ldap_first_entry($this->ds, $sr);
		do {
			$dn = ldap_get_dn($this->ds, $entry);
			$groupCn = strtolower(ldap_explode_dn($dn, 1)[0]);
			$results[$groupCn] = $dn;
		} while ($entry = ldap_next_entry($this->ds, $entry));

		foreach($results as $name => $dn) {
			if($dn === null) {
				throw new LdapException("Cannot find group '$name'", 1);
			}
		}

		if(count($results) !== count($names)) {
			throw new LdapException('Groups mismatch, converted names ' . implode(', ', $names) . ' to DNs ' . implode(' ', $results), 1);
		}

		return array_values($results);
	}

	private static function attrEquals($a, $b): bool {
		if(is_array($a) && is_array($b)) {
			return self::arrayEquals($a, $b);
		}
		return $a === $b;
	}

	private static function arrayEquals(array $a, array $b): bool {
		if(count($a) !== count($b)) {
			return false;
		}
		if(!empty(array_diff($a, $b))) {
			return false;
		}
		if(!empty(array_diff($b, $a))) {
			return false;
		}
		return true;
	}

	private static function isEmpty(string $attr, array $attributes): bool {
		return $attributes[$attr] === null || (is_array($attributes[$attr]) && count($attributes[$attr]) === 0);
	}

	public function getUrl(): string {
		return $this->url;
	}

	public function getStarttls(): bool {
		return $this->starttls;
	}

	private function simplifyAll($sr, array $attributes): array {
		$count = ldap_count_entries($this->ds, $sr);
		if($count === 0) {
			return [];
		}

		$entries = ldap_get_entries($this->ds, $sr);
		$simpler = [];
		foreach($entries as $k => $entry) {
			if($k !== 'count') {
				$user = self::simplify($entry);
				$user = self::fillAndSortAttributes($user, $attributes);
				$simpler[] = $user;
			}
		}

		return $simpler;
	}
}
