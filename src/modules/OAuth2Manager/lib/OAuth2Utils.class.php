<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	abstract class OAuth2Utils {

		/**
		 * @param PearDatabase $adb
		 * @param string $hostName
		 *
		 * @return string[]|null
		 */
		public static function fetchProviderData (PearDatabase $adb, $hostName) {
			if (empty ($hostName)) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT
					p.classname,
					p.clientid,
					p.clientsecrets,
					r.authenticationscopeoptions
				FROM
					vtiger_oauth2_resources r
					INNER JOIN vtiger_oauth2_providers p ON p.providername=r.providername
				WHERE
					r.resourcename=?',
				array ($hostName)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
			} else {
				$row = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $row;
		}

		public static function getProvider (PearDatabase $adb, $hostName, $redirectUri = null) {
			$providerData = OAuth2Utils::fetchProviderData ($adb, $hostName);
			if (!$providerData) {
				throw new Exception ("El recurso {$hostName} no se encuentra configurado");
			}

			$arguments = array (
				'clientId'     => $providerData ['clientid'],
				'clientSecret' => $providerData ['clientsecrets'],
				'accessType'   => 'offline',
			);
			if (!empty ($redirectUri)) {
				$arguments ['redirectUri'] = $redirectUri;
			}
			/** @var League\OAuth2\Client\Provider\AbstractProvider $provider */
			return new $providerData ['classname'] ($arguments);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $hostName
		 * @param \League\OAuth2\Client\Token\AccessTokenInterface $oldToken
		 *
		 * @return \League\OAuth2\Client\Token\AccessTokenInterface
		 * @throws Exception
		 */
		public static function refreshToken (PearDatabase $adb, $hostName, $oldToken) {
			$refreshToken = $oldToken->getRefreshToken ();
			if (empty ($refreshToken)) {
				return null;
			}

			/** @var League\OAuth2\Client\Provider\AbstractProvider $provider */
			$provider = self::getProvider ($adb, $hostName);
			return $provider->getAccessToken ('refresh_token', array ('refresh_token' => $oldToken->getRefreshToken ()));
		}

	}
