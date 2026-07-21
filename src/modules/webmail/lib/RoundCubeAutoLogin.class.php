<?php
	if (!defined ('INSTALL_PATH')) {
		define ('INSTALL_PATH', __DIR__ . '/../../../webmail/');
	}
	require_once ('webmail/program/include/iniset.php');

	class RoundCubeAutoLogin {
		/** @var string Roundcube link (with a trailing slash) */
		private $roundCubeUrl = '';

		/**
		 * Creates a new RC object
		 *
		 * @param string $roundCubeUrl The roundcube link
		 */
		public function __construct ($roundCubeUrl) {
			$this->roundCubeUrl = rtrim ($roundCubeUrl, '/') . '/';
		}

		/**
		 * Gets a token to use for the login
		 */
		private function getToken () {
			$ch = curl_init ($this->roundCubeUrl);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookiejar.txt');
			$response = curl_exec ($ch);
			curl_close ($ch);
			preg_match ('|<input type="hidden" name="_token" value="([A-z0-9]*)">|', $response, $matches);
			if ($matches) {
				return $matches [1];
			} else {
				return false;
			}
		}

		/**
		 * Tries to log a RC user in using cURL. Does two requests. One to
		 * get a session token to perform the login, and one to do the actual
		 * login of the user
		 *
		 * @param array $arguments Información necesaria para conectarse al servidor
		 * Formato:
		 * array (
		 *      'user' => array (
		 *          'email'    => 'fperez@corplaurus.int',
		 *          'fullname' => 'Felipe A. Pérez G.,
		 *          'password' => 'MiSuperDuperContraseña',
		 *          'username' => 'fperez@corplaurus.int',
		 *      ),
		 *      'incoming' => array (
		 *          'protocol'             => 'IMAP',
		 *          'hostname'             => 'router.corplaurus.int',
		 *          'port'                 => 143,
		 *          'securitytype'         => 'TLS',
		 *          'authenticationmethod' => 'PLAIN',
		 *      ),
		 *      'outgoing' => array (
		 *          'protocol'             => 'SMTP',
		 *          'hostname'             => 'router.corplaurus.int',
		 *          'port'                 => 25,
		 *          'securitytype'         => null,
		 *          'authenticationmethod' => 'PLAIN',
		 *      ),
		 * )
		 *
		 * @return array The cookies you should set with setcookie
		 * @throws Exception If something goes wrong
		 */
		public function login ($arguments) {
			if (empty ($arguments)) {
				throw new Exception ('No se han suministrado las credenciales de acceso al servidor de correo');
			}

			$token = $this->getToken ();
			if ($token === false) {
				throw new Exception ('El cliente de correo está mal configurado');
			}

			$security = !empty ($arguments ['incoming']['securitytype']) ? strtolower ($arguments ['incoming']['securitytype']) : '';
			$protocol = !empty ($security) ? "{$security}://" : '';

			$fields = array (
				'_action'   => 'login',
				'_host'     => "{$protocol}{$arguments ['incoming']['hostname']}:{$arguments ['incoming']['port']}",
				'_pass'     => rcmail::get_instance (0)->decrypt ($arguments ['user']['password']),
				'_task'     => 'login',
				'_timezone' => '',
				'_token'    => $token,
				'_url'      => '_task=login',
				'_user'     => $arguments ['user']['email'],
			);

			$ch = curl_init ($this->roundCubeUrl . '?_task=login');
			curl_setopt ($ch, CURLOPT_COOKIEFILE, 'cookiejar.txt');
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_HEADER, true);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query ($fields));
			$response     = curl_exec ($ch);
			$responseInfo = curl_getinfo ($ch);
			curl_close ($ch);
			if ($responseInfo ['http_code'] != 302) {
				throw new Exception ('Usuario o contraseña de correo inválidos');
			}
			$cookies = array ();
			// find all relevant cookies to set (php session + rc auth cookie)
			preg_match_all ('/Set-Cookie: (.*)\b/', $response, $responseCookies);
			foreach ($responseCookies [1] as $responseCookie) {
				preg_match ('|([A-z0-9\_]*)=([A-z0-9\_\-]*);|', $responseCookie, $matches);
				if ($matches) {
					$cookies [ $matches [1] ] = $matches [2];
				}
			}
			return $cookies;
		}

		/**
		 * Redirect to RC
		 *
		 * @param string $queryString
		 */
		public function redirect ($queryString = null) {
			if (empty ($queryString)) {
				$queryString = 'task=mail';
			}

			unlink ('cookiejar.txt');
			header ("Location: {$this->roundCubeUrl}?{$queryString}");
		}

	}
