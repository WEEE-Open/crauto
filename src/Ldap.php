<?php


namespace WEEEOpen\Crauto;

use InvalidArgumentException;

class Ldap {
	protected $ds;
	protected $groupsDn;
	protected $usersDn;
	protected static $multivalued = ['memberof' => true, 'sshPublicKey' => true];

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

	public function getInfo(string $uid, ?array $attributes = null): ?array {
		$uid = ldap_escape($uid, '', LDAP_ESCAPE_FILTER);
		$sr = ldap_search($this->ds, $this->usersDn, "(uid=$uid)", $attributes); // TODO: attributes
		if(!$sr) {
			throw new LdapException('Cannot search for uid');
		}
		$count = ldap_count_entries($this->ds, $sr);
		if($count === 0) {
			ldap_free_result($sr);
			return null;
		} else if($count > 1) {
			ldap_free_result($sr);
			throw new LdapException("$uid is not unique in $this->usersDn, $count results found");
		}

		//		$id = ldap_first_entry($this->ds, $sr);
		//		$dn = ldap_get_dn($this->ds, $id);
		$user = ldap_get_entries($this->ds, $sr)[0];
		ldap_free_result($sr);
		$user = self::simplify($user);
		if($attributes !== null) {
			$user = self::fillAndSortAttributes($user, $attributes);
		}
		return $user;
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
			if(array_key_exists($attribute, $result)) {
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
}
