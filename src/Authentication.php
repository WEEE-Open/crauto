<?php

namespace WEEEOpen\Crauto;

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;
use LogicException;
use ReflectionException;
use ReflectionMethod;
use stdClass;
use function Jumbojett\base64url_decode;

class Authentication
{
	private static $loggedIn = null;

	/**
	 * Users are required to log in to access this page. If they are not, execution stops and user is redirected to
	 * login page.
	 */
	public static function requireLogin()
	{
		$loggedIn = self::isLoggedIn();
		if (!$loggedIn) {
			self::redirectToLogin();
		}
	}

	/**
	 * Check that an user is logged correctly (i.e. valid id token). If the token is invalid, a refresh is attempted.
	 * If that fails too or there's no refresh token, then the user is not logged in.
	 *
	 * @return bool True if logged in, false if some action is needed to log in
	 */
	public static function isLoggedIn(): bool
	{
		if (self::$loggedIn === null) {
			self::$loggedIn = self::isLoggedInInternal();
		}

		return self::$loggedIn;
	}

	private static function isLoggedInInternal(): bool
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		if (!isset($_SESSION['expires'])) {
			return false;
		}

		if (CRAUTO_DEBUG_ALWAYS_REFRESH || self::idTokenExpired((int) $_SESSION['expires'])) {
			try {
				return self::performRefresh();
			} catch (LogicException | AuthenticationException $e) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Perform authentication. This stops script execution due to redirects.
	 *
	 * @throws OpenIDConnectClientException
	 */
	public static function authenticate()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		if (TEST_MODE) {
			error_log('TEST_MODE, faking authentication');
			switch(TEST_MODE_SSO) {
				case 1:
				default:
					$_SESSION['uid'] = 'test.administrator';
					$_SESSION['id'] = 'fake:example:68048769-c06d-4873-adf6-dbfa6b0afcd3';
					$_SESSION['cn'] = 'Test Administrator';
					$_SESSION['groups'] = ['HR'];
					$_SESSION['expires'] = PHP_INT_MAX;
					$_SESSION['refresh_token'] = 'refresh_token';
					$_SESSION['id_token'] = 'id_token';
					break;
				case 2:
					$_SESSION['uid'] = 'alice';
					$_SESSION['id'] = 'fake:example:9e071e1e-d0dd-4d58-9ac2-087ea0b41e97';
					$_SESSION['cn'] = 'Alice Test';
					$_SESSION['groups'] = ['Cloud', 'Gente', 'Riparatori'];
					$_SESSION['expires'] = PHP_INT_MAX;
					$_SESSION['refresh_token'] = 'refresh_token';
					$_SESSION['id_token'] = 'id_token';
					break;
				case 3:
					$_SESSION['uid'] = 'brodino';
					$_SESSION['id'] = 'fake:example:c476f0de-e554-439e-af4f-35c8bed02b9b';
					$_SESSION['cn'] = 'Bro Dino';
					$_SESSION['groups'] = ['Admin', 'Gente'];
					$_SESSION['expires'] = PHP_INT_MAX;
					$_SESSION['refresh_token'] = 'refresh_token';
					$_SESSION['id_token'] = 'id_token';
					break;
			}
		} else {
			$oidc = self::getOidc();
			//$oidc->setCertPath('/path/to/my.cert');
			$oidc->setRedirectURL(CRAUTO_URL . '/login.php');
			$oidc->addScope(['openid', 'profile']);
			$oidc->authenticate();
			self::setAttributes($oidc);
		}

		self::returnToPreviousPage();
	}

	/**
	 * Redirect to SSO server and log out. This stops script execution.
	 */
	public static function signOut()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$oidc = self::getOidc();
		$token = $_SESSION['id_token'];
		session_destroy();

		if (TEST_MODE) {
			error_log('TEST_MODE, no need to log out');
		} else {
			$oidc->signOut($token, CRAUTO_URL . '/logout_done.php');
		}
		exit;
	}

	/**
	 * @param int $expires timestamp from the "exp" claim
	 *
	 * @return bool True if id token is expired, false otherwise
	 */
	private static function idTokenExpired(int $expires): bool
	{
		return $expires <= time();
	}

	/**
	 * Redirect to login page and stop execution.
	 */
	private static function redirectToLogin()
	{
		$_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
		http_response_code(303);
		header("Location: /login.php");
		exit();
	}

	/**
	 * Perform refresh, using the refresh token stored in session
	 *
	 * @return bool If execution can continue or not (in case a redirect header has been set)
	 * @throws AuthenticationException
	 */
	private static function performRefresh(): bool
	{
		/*
		 * So, everyone is handling this thing differently.
		 * From what I've gathered, the best and most secure way to handle this is to issue a somewhat short lived
		 * refresh token (i.e. corresponds to session inactivity time) and even shorter id  tokens. When an id token
		 * expires, the application has up to "session inactivity time" seconds to get a new one via the refresh token,
		 * or the user is logged out.
		 *
		 * Example of someone claiming that this is legitimate:
		 * https://social.msdn.microsoft.com/Forums/en-US/8366f5ce-c479-45c3-9eea-2cff6285c94e/using-refreshtoken-as-session-timeout-oauth20-authentication
		 * This is all good and well, however "Refresh Tokens never expire": https://auth0.com/docs/tokens/refresh-token/current
		 *
		 * If a refresh token is compromised, an adversary might use it to obtain more id tokens without any limitation.
		 * To resolve this problem, the solution is simple: don't give out refresh tokens to users. Here, we'll be storing
		 * them in a PHP session. But what if the server is compromised? If we don't notice that, there's nothing
		 * that we can do, neither in this case nor in any other situation. If we do notice, we'll just revoke all refresh
		 * tokens (and possibly every other token) as soon as possible.
		 *
		 * WSO2 IS doesn't delete tokens when it's restarted.
		 * SAML2 single sign-out doesn't invalidate anything from OIDC sessions (so it's not a real single sign-out).
		 */
		if (!isset($_SESSION['refresh_token'])) {
			throw new LogicException('No refresh token available');
		}
		$oidc = self::getOidc(true);
		$json = $oidc->refreshToken($_SESSION['refresh_token']);

		// For an explanation, see:
		// https://github.com/WEEE-Open/tarallo/blob/89ffe90a042054cc40c886253a58ce64527b3420/src/HTTP/AuthManager.php#L344-L433

		if (isset($json->error)) {
			throw new AuthenticationException($json->error_description ?? $json->error);
		} elseif (isset($json->id_token) && isset($json->access_token)) {
			// Exhibit A: OIDC Tokens and Despair

			// This should return a new access token and a new refresh token.
			// WSO2 IS also provides a new id token, in the same reply, it's there... but this OIDC library doesn't expose
			// any public function to validate an id token, so there's no way to extract the updated claims and the new
			// expiry date. Or is it?
			// In implicit flow, the browser sends the id token to our client application. The authenticate() method
			// parses it. Let's fool the library into thinking this is an implicit flow authentication...
//			try {
//				$oidc->setAllowImplicitFlow(true);
//				$_REQUEST['id_token'] = $json->id_token;
//				$_REQUEST['access_token'] = $json->access_token;
//				$_SESSION['openid_connect_state'] = $_REQUEST['state'] = 'fake-implicit-flow';
//				$_SESSION['openid_connect_nonce'] = null;
//
//				// This is a very delicate system that is held up by toothpicks... the validation function (which is
//				// private, complicated, and called in a very long and complex method, so sublcassing the class is too
//				// painful to even attempt) expects a nonce in the message. Since it is generated by the client but
//				// cannot be set manually and never sent to the server in this execution path, the response obviously
//				// doesn't contain it (it just the same value, the server takes it from the request and puts it in the
//				// response). This generates a notice, but PHP helpfully returns null instead of an exception, so the
//				// checks still pass.
//				// The @ suppresses the useless notice. Yes, this is horrible, I know.
//				@$oidc->authenticate();
//			} catch(OpenIDConnectClientException $e) {
//				throw new AuthenticationException('Fake implicit flow failed', 0, $e);
//			} finally {
//				unset($_SESSION['openid_connect_nonce']);
//				unset($_SESSION['openid_connect_state']);
//				unset($_REQUEST['access_token']);
//				unset($_REQUEST['id_token']);
//				unset($_REQUEST['state']);
//			}

			try {
				// Validate the ID token signature
				$valid = $oidc->verifyJWTsignature($json->id_token);
				if (!$valid) {
					throw new AuthenticationException('Invalid JWT signature');
				}
			} catch (OpenIDConnectClientException $e) {
				throw new AuthenticationException('OpenIDConnectClientException: ' . $e->getMessage());
			}

			// Now decode the claims
			// decodeJWT() does exactly this
			$claims = json_decode(base64url_decode(explode(".", $json->id_token)[1]));

			try {
				// There's a comparison in verifyJWTclaims for the nonce set by the OIDC client. Which does not set any nonce.
				// This prevents replay attacks during redirects, but this a backend request, it's simply not possible to do
				// such an attack without taking control of the SSO server domain and generating a valid TLS certificate,
				// at which point the attacker has basically control of everything...
				// So we set it to the received value and the check passes.
				$oidc->setNonceForRefresh($claims->nonce);

				// We need to validate the claims. Possibly. Not entirely sure if this part is required.
				$method = new ReflectionMethod($oidc, 'verifyJWTclaims');
				$method->setAccessible(true);
				$valid = $method->invoke($oidc, $claims, $json->access_token);
				if (!$valid) {
					throw new AuthenticationException('verifyJWTclaims failed');
				}
			} catch (ReflectionException $e) {
				throw new AuthenticationException('ReflectionException: ' . $e->getMessage());
			} /** @noinspection PhpRedundantCatchClauseInspection */ catch (OpenIDConnectClientException $e) {
				throw new AuthenticationException('JWT claims validation failed: ' . $e->getMessage());
			}

			self::setAttributes($oidc, $claims, $json->id_token);

			return true;
		}
		// This could still be salvaged, by making another request to convert the new access token into an id token,
		// but it's not worth the effort, it just shouldn't happen with WSO2 IS and never happens in Keycloak (maybe?)
		throw new AuthenticationException('No id token in refresh token response');
	}

	private static function returnToPreviousPage()
	{
		// Comes from $_SERVER['REQUEST_URI'] which is already url encoded
		$location = $_SESSION['redirect_after_login'] ?? '/';
		http_response_code(303);
		header("Location: $location");
		unset($_SESSION['redirect_after_login']);
	}

	/**
	 * @param bool $refresh
	 *
	 * @return OpenIDConnectClient|OpenIDConnectRefreshClient
	 */
	private static function getOidc(bool $refresh = false)
	{
		require_once '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

		if ($refresh) {
			$oidc = new OpenIDConnectRefreshClient(CRAUTO_OIDC_ISSUER, CRAUTO_OIDC_CLIENT_KEY, CRAUTO_OIDC_CLIENT_SECRET);
		} else {
			$oidc = new OpenIDConnectClient(CRAUTO_OIDC_ISSUER, CRAUTO_OIDC_CLIENT_KEY, CRAUTO_OIDC_CLIENT_SECRET);
		}
		$oidc->addScope(['openid', 'profile']);
		$oidc->setVerifyHost(VERIFY_CERTIFICATES);
		$oidc->setVerifyPeer(VERIFY_CERTIFICATES);

		return $oidc;
	}

	private static function setAttributes(OpenIDConnectClient $oidc, $claims = null, ?string $idt = null)
	{
		if ($claims) {
			// Convert array (or stdObj) to stdObj.
			$claims = (object) $claims;
		} else {
			/** @noinspection PhpRedundantOptionalArgumentInspection */
			$claims = $oidc->getVerifiedClaims(null);
		}
		/** @var stdClass $claims */

		$uid = $claims->preferred_username;
		$id = $claims->sub;
		$cn = $claims->name;
		// WSO2 IS works as-is, for Keycloak go to clients > crauto > mappers > add builtin > groups
		$groups = $claims->groups;
		$exp = $claims->exp;
		$refresh_token = $oidc->getRefreshToken();
		$id_token = $idt ?? $oidc->getIdToken();

		$ldap = new Ldap(
			CRAUTO_LDAP_URL,
			CRAUTO_LDAP_BIND_DN,
			CRAUTO_LDAP_PASSWORD,
			CRAUTO_LDAP_USERS_DN,
			CRAUTO_LDAP_GROUPS_DN,
			CRAUTO_LDAP_STARTTLS
		);

		$ldapInfo = $ldap->getUser($uid, ['signedsir']);

		$_SESSION['uid'] = $uid;
		$_SESSION['id'] = $id;
		$_SESSION['cn'] = $cn;
		$_SESSION['signedsir'] = $ldapInfo['signedsir'] ?? false; // This won't updated until the next login but good enough
		$_SESSION['groups'] = $groups;
		$_SESSION['expires'] = $exp;

		// No new refresh token? We'll try the old one next time, the worst that could happen is that the server
		// rejects it
		$_SESSION['refresh_token'] = $refresh_token ?? $_SESSION['refresh_token'];
		$_SESSION['id_token'] = $id_token;
	}

//	/**
//	 * Split claimed groups into an array
//	 *
//	 * @param string $groups String returned by the OIDC library, e.g. "internal/everyone,HR,admin"
//	 *
//	 * @return string[] Groups, key and value are both the group name
//	 */
//	public static function splitGroups(?string $groups): array {
//		if($groups === null) {
//			return [];
//		}
//		$groups = explode(',', $groups);
//		return array_combine($groups, $groups); // a hashmap
//	}

	/**
	 * Check whether a user is an "admin" or not (part of HR group or not)
	 *
	 * @return bool
	 */
	public static function isAdmin(): bool
	{
		// For WSO2 IS:
		//$groups = Authentication::splitGroups($_SESSION['groups']);
		//return isset($groups['HR']);
		// For Keycloak:
		$groups = $_SESSION['groups'];
		return in_array('HR', $groups);
	}
}
