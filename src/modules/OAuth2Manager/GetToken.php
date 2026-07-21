<?php
	require_once ('include/MailManager/PlatzillaMailManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/OAuth2Manager/lib/OAuth2Utils.class.php');

	use League\OAuth2\Client\Provider\Google;

	global $adb;

	if (isset ($_SERVER ['HTTPS'])) {
		$protocol = (($_SERVER ['HTTPS']) && ($_SERVER ['HTTPS']) != 'off') ? 'https' : 'http';
	} else {
		$protocol = 'http';
	}
	$redirectUri = "{$protocol}://{$_SERVER ['HTTP_HOST']}{$_SERVER ['PHP_SELF']}?module=OAuth2Manager&action=GetToken&Popup=true";
	$hostName    = PlatzillaUtils::purify ($_GET, 'hostname', $_SESSION ['oauth2hostname']);
	$redirectTo  = PlatzillaUtils::purify ($_GET, 'redirectto', $_SESSION ['oauth2redirectto']);

	try {
		if (isset ($_GET ['hostname'])) {
			$_SESSION ['oauth2hostname'] = $_GET ['hostname'];
		}
		if (isset ($_GET ['redirectto'])) {
			$_SESSION ['oauth2redirectto'] = $_GET ['redirectto'];
		}

		if (!empty ($hostName)) {
			// Iniciando solicitud
			$providerData = OAuth2Utils::fetchProviderData ($adb, $hostName);
			if (!$providerData) {
				throw new Exception ("El recurso {$hostName} no se encuentra configurado");
			}

			$authenticationOptions = json_decode ($providerData ['authenticationscopeoptions'], true);
			/** @var League\OAuth2\Client\Provider\AbstractProvider $provider */
			$provider = new $providerData ['classname'] (array (
				'clientId'        => $providerData ['clientid'],
				'clientSecret'    => $providerData ['clientsecrets'],
				'redirectUri'     => $redirectUri,
				'accessType'      => 'offline',
				'approval_prompt' => 'force',
			));
		} else {
			$authenticationOptions = null;
			$provider              = null;
		}

		if (empty ($provider)) {
			throw new Exception ('No encontramos la configuración de tu proveedor');
		} else if (!empty ($_GET ['error'])) {
			throw new Exception (htmlspecialchars ($_GET ['error'], ENT_QUOTES, 'UTF-8'));
		} else if ((!empty ($_GET ['state'])) && ($_GET ['state'] !== $_SESSION ['oauth2state'])) {
			unset ($_SESSION ['oauth2state']);
			throw new Exception ('Estado inválido');
		}

		if (empty ($_GET ['code'])) {
			// If we don't have an authorization code then get one
			$authorizationUrl         = $provider->getAuthorizationUrl ($authenticationOptions);
			$_SESSION ['oauth2state'] = $provider->getState ();
			header ("Location: {$authorizationUrl}");
		} else {
			// Try to get an access token (using the authorization code grant)
			$token = $provider->getAccessToken ('authorization_code', array ('code' => $_GET ['code']));
			if (empty ($token)) {
				throw new Exception ('Se recibió un respuesta inesperada');
			}

			$owner     = $provider->getResourceOwner ($token);
			$ownerData = $owner->toArray ();

			$_SESSION ['oauth2token'] = json_encode ($token->jsonSerialize ());
			$_SESSION ['oauth2user']  = $owner->getEmail ();
			header ("Location: {$_SESSION ['oauth2redirectto']}");
		}
	} catch (Exception $e) {
		unset ($_SESSION ['oauth2token']);
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ("Location: {$_SESSION ['oauth2redirectto']}");
	}
	exit ();
