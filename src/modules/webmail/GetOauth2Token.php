<?php
	if (!function_exists ('random_bytes')) {
		function random_bytes ($length) {
			return openssl_random_pseudo_bytes ($length);
		}
	}
	require_once ('include/MailManager/vendor/autoload.php');

	use League\OAuth2\Client\Provider\Google;

	global $oAuthProviders;

	unset ($_SESSION ['oauth2token']);
	$hostName      = !empty ($_GET ['hostname']) ? $_GET ['hostname'] : $_SESSION ['hostname'];
	$redirectUri = 'http://localhost:8080/home.platzilla.tld/index.php?module=webmail&action=GetOauth2Token&Popup=true';

	try {
		switch ($hostName) {
			case 'imap.gmail.com':
				$clientId       = $oAuthProviders [ $hostName ]['web']['client_id'];
				$clientSecret   = $oAuthProviders [ $hostName ]['web']['client_secret'];
				$provider       = new Google ([
					'clientId'     => $clientId,
					'clientSecret' => $clientSecret,
					'redirectUri'  => $redirectUri,
					'accessType'   => 'offline',
				]);
				break;
			default:
				$provider = null;
				break;
		}

		if (empty ($provider)) {
			throw new Exception ('Provider not found');
		} else if (!empty ($_GET ['error'])) {
			throw new Exception ('Got error: ' . htmlspecialchars ($_GET ['error'], ENT_QUOTES, 'UTF-8'));
		} else if ((!empty ($_GET ['state'])) && ($_GET ['state'] !== $_SESSION ['oauth2state'])) {
			unset ($_SESSION ['oauth2state']);
			throw new Exception ('Invalid state');
		}

		$_SESSION ['hostname'] = $hostName;
		if (empty ($_GET ['code'])) {
			// If we don't have an authorization code then get one
			$authUrl                  = $provider->getAuthorizationUrl ([
				'scope' => [
					'https://mail.google.com/',
				],
			]);
			$_SESSION ['oauth2state'] = $provider->getState ();
			header ('Location: ' . $authUrl);
		} else {
			// Try to get an access token (using the authorization code grant)
			$token = $provider->getAccessToken ('authorization_code', [
				'code' => $_GET ['code'],
			]);

			if (empty ($token)) {
				throw new Exception ('Empty token');
			}

			$owner = $provider->getResourceOwner ($token);
			$ownerDetails = $owner->toArray ();

			$_SESSION ['oauth2token'] = json_encode ($token->jsonSerialize ());
			$_SESSION ['oauth2user'] = $ownerDetails ['email'];
			header ('Location: http://localhost:8080/home.platzilla.tld/index.php?module=webmail&action=AccountEditView');
		}
	} catch (Exception $e) {
		unset ($_SESSION ['oauth2token']);
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ('Location: http://localhost:8080/home.platzilla.tld/index.php?module=webmail&action=AccountEditView');
	}
	exit ();
