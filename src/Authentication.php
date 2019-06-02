<?php


namespace WEEEOpen\Crauto;

use Jumbojett\OpenIDConnectClient;

use Jumbojett\OpenIDConnectClientException;
use LogicException;

class Authentication {
	// This is a class just to exploit the autoloading functionality
	public static function requireLogin(): bool {
		if(session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		if(!isset($_SESSION['expires'])) {
			return self::redirectToLogin();
		}

		if(self::idTokenExpired((int) $_SESSION['expires'])) {
			try {
				return self::performRefresh();
			} catch(LogicException $e) {
				return self::redirectToLogin();
			} catch(AuthenticationException $e) {
				return self::redirectToLogin();
			}
		}
		return true;
	}

	public static function authenticate(): bool {
		if(session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$oidc = self::getOidc();
		//$oidc->setCertPath('/path/to/my.cert');
		$oidc->setRedirectURL(CRAUTO_URL . '/login.php');
		$oidc->authenticate();

		self::setAttributes($oidc);

		self::returnToPreviousPage();
		return false;
	}

	public static function signOut() {
		if(session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$oidc = self::getOidc();
		$at = $_SESSION['access_token'];
		session_destroy();
		$oidc->signOut($at, CRAUTO_URL . '/logout.php');
	}

	private static function idTokenExpired(int $expires) {
		return $expires <= time();
	}

	private static function redirectToLogin(): bool {
		$_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
		http_response_code(303);
		header("Location: /login.php");
		return false;
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

				$oidc->authenticate();
			} catch(OpenIDConnectClientException $e) {
				throw new AuthenticationException('Fake implicit flow failed', 0, $e);
			} finally {
				unset($_REQUEST['access_token']);
				unset($_REQUEST['id_token']);
				unset($_SESSION['openid_connect_state']);
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
		$access_token = $oidc->getAccessToken();

		$_SESSION['uid'] = $uid;
		$_SESSION['cn'] = $cn;
		$_SESSION['expires'] = $exp;
		$_SESSION['refresh_token'] = $refresh_token;
		$_SESSION['access_token'] = $access_token;
	}
}
