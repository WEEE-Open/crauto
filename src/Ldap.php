<?php


namespace WEEEOpen\Crauto;

class Ldap {
	protected $ds;
	protected $groupsDn;
	protected $usersDn;

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

	public function getInfo(string $uid): ?array {
		$uid = ldap_escape($uid, '', LDAP_ESCAPE_FILTER);
		$sr = ldap_search($this->ds, $this->usersDn, "(uid=$uid)"); // TODO: attributes
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

		return self::simplify(ldap_get_entries($this->ds, $sr)[0]);
//		$id = ldap_first_entry($this->ds, $sr);
//		$dn = ldap_get_dn($this->ds, $id);
//		ldap_free_result($sr);
	}

	protected static function simplify(array $result): array {
		$things = [];
		foreach($result as $k => $v) {
			if(!is_int($k) && $k !== 'count') {
				$attr = strtolower($k); // Should be already done, but repeat it anyway
				if($v['count'] === 1) {
					$things[$attr] = $v[0];
				} else {
					$things[$attr] = array_diff_key($v, ['count']);
				}
			}
		}
		return $things;
	}
}
