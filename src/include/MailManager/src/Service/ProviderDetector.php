<?php
	namespace Platzilla\MailManager\Service;

	use Platzilla\MailManager\Type\AuthenticationMethod;
	use Platzilla\MailManager\Type\SecurityType;
	use Platzilla\MailManager\Type\ServiceType;
	use Platzilla\MailManager\Type\UserNameType;
	use Platzilla\MailManager\Provider\GenericProvider;

	abstract class ProviderDetector {
		const MOZILLA_ISP_DATABASE_URL = 'https://autoconfig.thunderbird.net/v1.1/%s';

		/**
		 * @param string $domain
		 * @param string[] $possibleHostNames
		 *
		 * @return string[]|null
		 */
		private static function buildPossibleFullyQualifiedHostNames ($domain, $possibleHostNames) {
			$hostNames = array ();
			foreach ($possibleHostNames as $possibleHostName) {
				$hostNames [] = "{$possibleHostName}.{$domain}";
			}

			if ((getmxrr ($domain, $mxRecords) === false) || (empty ($mxRecords))) {
				return $hostNames;
			}

			$mxRecords = array_filter ($mxRecords);
			foreach ($mxRecords as $mxRecord) {
				$mxDomainParts = array_slice (explode ('.', $mxRecord), 1);
				$n             = count ($mxDomainParts);
				while ($n > 1) {
					$mxDomain = join ('.', $mxDomainParts);
					foreach ($possibleHostNames as $possibleHostName) {
						$hostNames [] = "{$possibleHostName}.{$mxDomain}";
					}
					array_shift ($mxDomainParts);
					$n = count ($mxDomainParts);
				}
			}

			return array_unique ($hostNames);
		}

		/**
		 * @param string $domain
		 *
		 * @return string[]|null
		 */
		private static function detectIncomingSettings ($domain) {
			$possibleHostNames = array ('mail', 'imap', 'imap.mail', 'imap-mail', 'pop');
			$hostNames         = self::buildPossibleFullyQualifiedHostNames ($domain, $possibleHostNames);

			foreach ($hostNames as $hostName) {
				$securityType = self::testImapAccess ($hostName, ServiceType::IMAP_DEFAULT_PORT);
				if ($securityType == SecurityType::STARTTLS) {
					return array (
						'hostname'     => $hostName,
						'port'         => ServiceType::IMAP_DEFAULT_PORT,
						'securitytype' => $securityType,
						'service'      => ServiceType::IMAP,
					);
				}

				$securityType = self::testImapAccess ($hostName, ServiceType::IMAP_SSL_PORT, SecurityType::SSL);
				if (!empty ($securityType)) {
					return array (
						'hostname'     => $hostName,
						'port'         => ServiceType::IMAP_SSL_PORT,
						'securitytype' => $securityType,
						'service'      => ServiceType::IMAP,
					);
				}

				$securityType = self::testPopAccess ($hostName, ServiceType::POP3_SSL_PORT, SecurityType::SSL);
				if (!empty ($securityType)) {
					return array (
						'hostname'     => $hostName,
						'port'         => ServiceType::POP3_SSL_PORT,
						'securitytype' => $securityType,
						'service'      => ServiceType::POP3,
					);
				}

				$securityType = self::testPopAccess ($hostName, ServiceType::POP3_DEFAULT_PORT);
				if (!empty ($securityType)) {
					return array (
						'hostname'     => $hostName,
						'port'         => ServiceType::POP3_DEFAULT_PORT,
						'securitytype' => $securityType,
						'service'      => ServiceType::POP3,
					);
				}
			}
			return null;
		}

		/**
		 * @param string $domain
		 *
		 * @return GenericProvider|null
		 */
		private static function detectOutgoingSettings ($domain) {
			$possibleHostNames = array ('mail', 'smtp', 'smtp.mail', 'smtp-mail');
			$hostNames         = self::buildPossibleFullyQualifiedHostNames ($domain, $possibleHostNames);

			foreach ($hostNames as $hostName) {
				$securityType = self::testSmtpAccess ($hostName, ServiceType::SMTP_STARTTLS_PORT);
				if ($securityType == SecurityType::STARTTLS) {
					return array (
						'hostname'     => $hostName,
						'port'         => ServiceType::SMTP_STARTTLS_PORT,
						'securitytype' => $securityType,
						'service'      => ServiceType::SMTP,
					);
				}

				$securityType = self::testSmtpAccess ($hostName, ServiceType::SMTP_DEFAULT_PORT);
				if ($securityType == SecurityType::STARTTLS) {
					return array (
						'hostname'     => $hostName,
						'port'         => ServiceType::SMTP_DEFAULT_PORT,
						'securitytype' => $securityType,
						'service'      => ServiceType::SMTP,
					);
				}

				$securityType = self::testSmtpAccess ($hostName, ServiceType::SMTP_SSL_PORT, SecurityType::SSL);
				if (!empty ($securityType)) {
					return array (
						'hostname'     => $hostName,
						'port'         => ServiceType::SMTP_SSL_PORT,
						'securitytype' => $securityType,
						'service'      => ServiceType::SMTP,
					);
				}
			}
			return null;
		}

		/**
		 * @param string $domain
		 *
		 * @return string|null
		 */
		private static function doHttpRequest ($domain) {
			$ch = curl_init ();
			curl_setopt ($ch, CURLOPT_FORBID_REUSE, true);
			curl_setopt ($ch, CURLOPT_FRESH_CONNECT, true);
			curl_setopt ($ch, CURLOPT_HEADER, false);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_URL, sprintf (self::MOZILLA_ISP_DATABASE_URL, $domain));
			$response = curl_exec ($ch);
			$httpcode = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
			curl_close ($ch);
			return (intval ($httpcode) / 100) == 2 ? $response : null;
		}

		/**
		 * @param string $domain
		 *
		 * @return GenericProvider|null
		 */
		private static function fetchFromMozillaIspDatabase ($domain) {
			$response = self::doHttpRequest ($domain);
			/** @var object $xml */
			$xml = simplexml_load_string ($response);
			if ($xml === false) {
				return null;
			}

			$imapSettings = null;
			$popSettings  = null;
			foreach ($xml->emailProvider->incomingServer as $incomingServer) {
				$service = strtolower ($incomingServer ['type']);
				switch ($service) {
					case ServiceType::IMAP:
						$imapSettings = array (
							'authenticationmethod' => strtolower ($incomingServer->authentication),
							'hostname'             => strtolower ($incomingServer->hostname),
							'port'                 => intval ($incomingServer->port),
							'securitytype'         => strtolower ($incomingServer->socketType),
							'service'              => ServiceType::IMAP,
							'usernametype'         => strtolower ($incomingServer->username),
						);
						break;
					case ServiceType::POP3:
						$popSettings = array (
							'authenticationmethod' => strtolower ($incomingServer->authentication),
							'hostname'             => strtolower ($incomingServer->hostname),
							'port'                 => intval ($incomingServer->port),
							'securitytype'         => strtolower ($incomingServer->socketType),
							'service'              => ServiceType::POP3,
							'usernametype'         => strtolower ($incomingServer->username),
						);
						break;
					default:
						// Protocolo no válido. Ignorar
						break;
				}
			}

			$smtpSettings = null;
			foreach ($xml->emailProvider->outgoingServer as $outgoingServer) {
				$service = strtolower ($outgoingServer ['type']);
				switch ($service) {
					case ServiceType::SMTP:
						$smtpSettings = array (
							'authenticationmethod' => strtolower ($outgoingServer->authentication),
							'hostname'             => strtolower ($outgoingServer->hostname),
							'port'                 => intval ($outgoingServer->port),
							'securitytype'         => strtolower ($outgoingServer->socketType),
							'service'              => ServiceType::SMTP,
							'usernametype'         => strtolower ($outgoingServer->username),
						);
						break;
					default:
						// Protocolo no válido. Ignorar
						break;
				}
			}

			if (((!empty ($imapSettings)) || (!empty ($popSettings))) && (!empty ($smtpSettings))) {
				$incomingSettings = !empty ($imapSettings) ? $imapSettings : $popSettings;
				$providerData = array (
					'incominghostname'             => $incomingSettings ['hostname'],
					'incomingport'                 => $incomingSettings ['port'],
					'incomingsecuritytype'         => $incomingSettings ['securitytype'],
					'incomingservice'              => $incomingSettings ['service'],
					'incomingauthenticationmethod' => $incomingSettings ['authenticationmethod'],
					'incomingusernametype'         => $incomingSettings ['usernametype'],
					'outgoinghostname'             => $smtpSettings ['hostname'],
					'outgoingport'                 => $smtpSettings ['port'],
					'outgoingsecuritytype'         => $smtpSettings ['securitytype'],
					'outgoingservice'              => $smtpSettings ['service'],
					'outgoingauthenticationmethod' => $smtpSettings ['authenticationmethod'],
					'outgoingusernametype'         => $smtpSettings ['usernametype'],
				);
				return new GenericProvider ($providerData);
			} else {
				return null;
			}
		}

		/**
		 * @param string $hostName
		 * @param integer $port
		 * @param string $securityType
		 *
		 * @return string|null
		 */
		private static function testImapAccess ($hostName, $port, $securityType = null) {
			if ($securityType == SecurityType::SSL) {
				$protocol = 'ssl';
			} else if ($securityType == SecurityType::STARTTLS) {
				$protocol = 'tcp';
			} else {
				$protocol = 'tcp';
			}

			$fp = @fsockopen ("{$protocol}://{$hostName}", $port, $errno, $errstr, 2);
			if (!$fp) {
				return null;
			}

			$response = fread ($fp, 512);
			fclose ($fp);
			if (strpos ($response, '* OK') !== 0) {
				return null;
			}

			if (strpos ($response, 'STARTTLS') !== false) {
				$confirmedSecurityType = SecurityType::STARTTLS;
			} else if ($protocol == SecurityType::SSL) {
				$confirmedSecurityType = SecurityType::SSL;
			} else {
				$confirmedSecurityType = SecurityType::PLAIN;
			}

			return $confirmedSecurityType;
		}

		/**
		 * @param string $hostName
		 * @param integer $port
		 * @param string|null $securityType
		 *
		 * @return string|null
		 */
		private static function testPopAccess ($hostName, $port, $securityType = null) {
			if ($securityType == SecurityType::SSL) {
				$protocol = 'ssl';
			} else {
				$protocol = 'tcp';
			}

			$fp = @fsockopen ("{$protocol}://{$hostName}", $port, $errno, $errstr, 2);
			if (!$fp) {
				return null;
			}
			$response = fread ($fp, 512);
			fclose ($fp);
			if (strpos ($response, '+OK') !== 0) {
				return null;
			}

			if (strpos ($response, 'STLS') !== false) {
				$confirmedSecurityType = SecurityType::STARTTLS;
			} else if ($protocol == SecurityType::SSL) {
				$confirmedSecurityType = SecurityType::SSL;
			} else {
				$confirmedSecurityType = SecurityType::PLAIN;
			}

			return $confirmedSecurityType;
		}

		/**
		 * @param string $hostName
		 * @param integer $port
		 * @param string|null $securityType
		 *
		 * @return null|string
		 */
		private static function testSmtpAccess ($hostName, $port, $securityType = null) {
			if ($securityType == SecurityType::SSL) {
				$protocol = 'ssl';
			} else {
				$protocol = 'tcp';
			}

			$fp = @fsockopen ("{$protocol}://{$hostName}", $port, $errno, $errstr, 2);
			if (!$fp) {
				return null;
			}

			$response = fread ($fp, 512);
			if (empty (trim ($response))) {
				return null;
			}

			fwrite ($fp, "ehlo localhost\r\n");
			$response = fread ($fp, 512);
			fclose ($fp);
			if (empty (trim ($response))) {
				return null;
			}

			if (strpos ($response, 'STARTTLS') !== false) {
				$confirmedSecurityType = SecurityType::STARTTLS;
			} else if ($protocol == SecurityType::SSL) {
				$confirmedSecurityType = SecurityType::SSL;
			} else {
				$confirmedSecurityType = SecurityType::PLAIN;
			}

			return $confirmedSecurityType;
		}

		/**
		 * @param string $emailAddress
		 *
		 * @return GenericProvider[]|GenericProvider|null
		 */
		public static function detect ($emailAddress) {
			if ((empty ($emailAddress)) || (filter_var ($emailAddress, FILTER_VALIDATE_EMAIL) === false)) {
				return null;
			}

			$domain = strtolower (substr ($emailAddress, (strpos ($emailAddress, '@') + 1)));

			$provider = self::fetchFromMozillaIspDatabase ($domain);
			if (!empty ($provider)) {
				return $provider;
			}

			$incomingSettings = self::detectIncomingSettings ($domain);
			$outgoingSettings = self::detectOutgoingSettings ($domain);
			if ((!empty ($incomingSettings)) && (!empty ($outgoingSettings))) {
				$providerData = array (
					'incomingauthenticationmethod' => AuthenticationMethod::PASSWORD_CLEAR_TEXT,
					'incominghostname'             => $incomingSettings ['hostname'],
					'incomingport'                 => $incomingSettings ['port'],
					'incomingsecuritytype'         => $incomingSettings ['securitytype'],
					'incomingservice'              => $incomingSettings ['service'],
					'incomingusernametype'         => UserNameType::EMAIL_ADDRESS,
					'outgoingauthenticationmethod' => AuthenticationMethod::PASSWORD_CLEAR_TEXT,
					'outgoinghostname'             => $outgoingSettings ['hostname'],
					'outgoingport'                 => $outgoingSettings ['port'],
					'outgoingsecuritytype'         => $outgoingSettings ['securitytype'],
					'outgoingservice'              => $outgoingSettings ['service'],
					'outgoingusernametype'         => UserNameType::EMAIL_ADDRESS,
				);
				return new GenericProvider ($providerData);
			} else {
				return null;
			}
		}

		/**
		 * @param GenericProvider $provider
		 *
		 * @return boolean
		 */
		public static function test ($provider) {
			if (!($provider instanceof GenericProvider)) {
				return false;
			}

			$incomingService = $provider->getIncomingService ();
			if ($incomingService == ServiceType::IMAP) {
				$result = self::testImapAccess ($provider->getIncomingHostName (), $provider->getIncomingPort (), $provider->getIncomingSecurityType ());
			} else if ($incomingService == ServiceType::POP3) {
				$result = self::testPopAccess ($provider->getIncomingHostName (), $provider->getIncomingPort (), $provider->getIncomingSecurityType ());
			}

			if (empty ($result)) {
				return false;
			}

			return self::testSmtpAccess ($provider->getOutgoingHostName (), $provider->getOutgoingPort (), $provider->getOutgoingSecurityType ());
		}

	}
