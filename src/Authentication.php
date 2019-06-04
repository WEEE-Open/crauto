<?php


namespace WEEEOpen\Crauto;

use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;
use LogicException;

class Authentication {
	// This is a class just to exploit the autoloading functionality

	/**
	 * Users are required to log in to access this page. If they are not, execution stops and user is redirected to
	 * login page.
	 */
	public static function requireLogin() {
		$loggedIn = self::isLoggedIn();
		if(!$loggedIn) {
			self::redirectToLogin();
		}
	}

	/**
	 * Check that an user is logged correctly (i.e. valid id token). If the token is invalid, a refresh is attempted.
	 * If that fails too or there's no refresh token, then the user is not logged in.
	 *
	 * @return bool True if logged in, false if some action is needed to log in
	 */
	public static function isLoggedIn(): bool {
		if(session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		if(!isset($_SESSION['expires'])) {
			return false;
		}

		if(CRAUTO_DEBUG_ALWAYS_REFRESH || self::idTokenExpired((int) $_SESSION['expires'])) {
			try {
				return self::performRefresh();
			} catch(LogicException $e) {
				return false;
			} catch(AuthenticationException $e) {
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
	public static function authenticate() {
		if(session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$oidc = self::getOidc();
		//$oidc->setCertPath('/path/to/my.cert');
		$oidc->setRedirectURL(CRAUTO_URL . '/login.php');
		$oidc->authenticate();

		self::setAttributes($oidc);

		self::returnToPreviousPage();
	}

	/**
	 * Redirect to SSO server and log out. This stops script execution.
	 */
	public static function signOut() {
		if(session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$oidc = self::getOidc();
		$token = $_SESSION['id_token'];
		session_destroy();
		$oidc->signOut($token, CRAUTO_URL . '/logout.php');
		exit();
	}

	/**
	 * @param int $expires timestamp from the "exp" claim
	 *
	 * @return bool True if id token is expired, false otherwise
	 */
	private static function idTokenExpired(int $expires) {
		return $expires <= time();
	}

	/**
	 * Redirect to login page and stop execution.
	 */
	private static function redirectToLogin() {
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
	private static function performRefresh(): bool {
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
		 * that we can do, neither in this case nor in any other situation. If we do notice, we'll yust revoke all refresh
		 * tokens (and possibly every other token) as soon as possible.
		 *
		 * TODO: What happens after single sign-out on WSO2 IS? Does it revoke refresh tokens? Also, when it is restarted, does it revoke tokens?
		 * Because "Restarting WSO2 IS" may be a really quick (and dirty) solution to immediately log everyone out and revoke all tokens,
		 * if some refresh tokens get compromised...
		 */
		if(!isset($_SESSION['refresh_token'])) {
			throw new LogicException('No refresh token available');
		}
		$oidc = self::getOidc();
		$json = $oidc->refreshToken($_SESSION['refresh_token']);
		if(isset($json->error)) {
			throw new AuthenticationException(isset($json->error_description) ? $json->error_description : $json->error);
		} elseif(isset($json->id_token) && isset($json->access_token)) {
			// This should return a new access token and a new refresh token.
			// WSO2 IS also provides a new id token, in the same reply, it's there... but this OIDC library doesn't expose
			// any public function to validate an id token, so there's no way to extract the updated claims and the new
			// expiry date. Or is it?
			// In implicit flow, the browser sends the id token to our client application. The authenticate() method
			// parses it. Let's fool the library into thinking this is an implicit flow authentication...
			try {
				$oidc->setAllowImplicitFlow(true);
				$_REQUEST['id_token'] = $json->id_token;
				$_REQUEST['access_token'] = $json->access_token;
				$_SESSION['openid_connect_state'] = $_REQUEST['state'] = 'fake-implicit-flow';
				$_SESSION['openid_connect_nonce'] = null;

				// This is a very delicate system that is held up by toothpicks... the validation function (which is
				// private, complicated, and called in a very long and complex method, so sublcassing the class is too
				// painful to even attempt) expects a nonce in the message. Since it is generated by the client but
				// cannot be set manually and never sent to the server in this execution path, the response obviously
				// doesn't contain it (it just the same value, the server takes it from the request and puts it in the
				// response). This generates a notice, but PHP helpfully returns null instead of an exception, so the
				// checks still pass.
				// The @ suppresses the useless notice. Yes, this is horrible, I know.
				@$oidc->authenticate();
			} catch(OpenIDConnectClientException $e) {
				throw new AuthenticationException('Fake implicit flow failed', 0, $e);
			} finally {
				unset($_SESSION['openid_connect_nonce']);
				unset($_SESSION['openid_connect_state']);
				unset($_REQUEST['access_token']);
				unset($_REQUEST['id_token']);
				unset($_REQUEST['state']);
			}

			self::setAttributes($oidc);

			return true;
		}
		// This could still be salvaged, by making another request to convert the new access token into an id token,
		// but it's not worth the effort, it just shouldn't happen with WSO2 IS
		throw new AuthenticationException('No id token in refresh token response');
	}

	private static function returnToPreviousPage() {
		// TODO: urlencode? Escape? Do something?
		$location = $_SESSION['redirect_after_login'] ?? '/';
		http_response_code(303);
		header("Location: $location");
		unset($_SESSION['redirect_after_login']);
	}

	private static function getOidc() {
		require_once '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

		$oidc = new OpenIDConnectClient(CRAUTO_OIDC_ISSUER, CRAUTO_OIDC_CLIENT_KEY, CRAUTO_OIDC_CLIENT_SECRET);
		// TODO: $oidc->addScope(['openid', 'profile']);
		$oidc->setVerifyHost(false);
		$oidc->setVerifyPeer(false);

		return $oidc;
	}

	private static function setAttributes(OpenIDConnectClient $oidc) {
		$uid = $oidc->requestUserInfo('sub');
		$cn = $oidc->requestUserInfo('name');
		$exp = $oidc->getVerifiedClaims('exp');
		$refresh_token = $oidc->getRefreshToken();
		$id_token = $oidc->getIdToken();

		$_SESSION['uid'] = $uid;
		$_SESSION['cn'] = $cn;
		$_SESSION['expires'] = $exp;
		$_SESSION['refresh_token'] = $refresh_token;
		$_SESSION['id_token'] = $id_token;
	}
}
