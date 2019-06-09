<?php


namespace WEEEOpen\Crauto;

use InvalidArgumentException;

class Ldap {
	protected $ds;
	protected $groupsDn;
	protected $usersDn;
	protected static $multivalued = ['memberof' => true, 'sshpublickey' => true];

	public function __construct(string $url, string $bindDn, string $password, string $usersDn, string $groupsDn, bool $startTls = true) {
		$this->groupsDn = $groupsDn;
		$this->usersDn = $usersDn;
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

	public function getUser(string $uid, ?array $attributes = null): ?array {
		$sr = $this->searchByUid($uid, $attributes);
		$user = ldap_get_entries($this->ds, $sr)[0];
		$user = self::simplify($user);
		if($attributes !== null) {
			$user = self::fillAndSortAttributes($user, $attributes);
		}
		return $user;
	}

	public function getUsers(array $attributes) {
		if(version_compare(PHP_VERSION, '7.3', '>=')) {
			// From PHP 7.3 onward, the serverctrls parameter is available. This is the only reasonable way to have the results sorted.
			// Well, if there's more than one page which is actually not supported, but let's use it anyway.
			/** @noinspection PhpUndefinedConstantInspection */
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
			/** @noinspection PhpMethodParametersCountMismatchInspection */
			$sr = ldap_search($this->ds, $this->usersDn, '(uid=*)', $attributes, null, null, null, null, $serverctrls);
		} else {
			$sr = ldap_search($this->ds, $this->usersDn, '(uid=*)', $attributes);
		}
		if(!$sr) {
			throw new LdapException('Cannot search users');
		}

		$count = ldap_count_entries($this->ds, $sr);
		if($count === 0) {
			return [];
		}

		$entries = ldap_get_entries($this->ds, $sr);
		$simpler = [];
		foreach($entries as $k => $entry) {
			if($k !== 'count') {
				$simpler[] = self::simplify($entry);
			}
		}
		usort($simpler, function(array $a, array $b): int { return strcmp($a['uid'], $b['uid']); });
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

	public function updateUser(string $uid, array $replace, array $previous) {
		$sr = $this->searchByUid($uid, ['dn']);
		$theOnlyResult = ldap_first_entry($this->ds, $sr);
		$dn = ldap_get_dn($this->ds, $theOnlyResult);

		$modlist = [];
		foreach($replace as $attr => $values) {
			if($attr === 'memberof') {
				continue;
			}
			if($values === '' || $values === []) {
				if(!self::isEmpty($attr, $previous)) {
					// Actually delete (had a value, now has none)
					$modlist[] = [
						"attrib"  => $attr,
						"modtype" => LDAP_MODIFY_BATCH_REMOVE_ALL
					];
				}
			} else {
				if(!is_array($values)) {
					$values = [$values];
				}
				if(!self::isEmpty($attr, $previous)) {
					// Attribute already exists: replace
					$modlist[] = [
						"attrib"  => $attr,
						"modtype" => LDAP_MODIFY_BATCH_REPLACE,
						"values"  => $values
					];
				} else {
					// Does not exist: add
					$modlist[] = [
						"attrib"  => $attr,
						"modtype" => LDAP_MODIFY_BATCH_ADD,
						"values"  => $values
					];
				}
			}
		}
		if(isset($replace['memberof'])) {
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
		$result = ldap_modify_batch($this->ds, $dn, $modlist);
		if($result === false) {
			throw new LdapException('Modification failed (' . ldap_error($this->ds) . ')');
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
				$sorted[$attribute] = null;
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

	private static function isEmpty(string $attr, array $attributes) {
		return $attributes[$attr] === null || (is_array($attributes[$attr]) && count($attributes[$attr]) === 0);
	}
}
