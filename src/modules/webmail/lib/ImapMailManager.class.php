<?php
	require_once ('include/php-imap-client/ImapClient/ImapClientException.php');
	require_once ('include/php-imap-client/ImapClient/ImapClient.php');
	require_once ('include/php-imap-client/ImapClient/ImapConnect.php');
	require_once ('include/php-imap-client/ImapClient/IncomingMessage.php');
	require_once ('include/php-imap-client/ImapClient/TypeAttachments.php');
	require_once ('include/php-imap-client/ImapClient/TypeBody.php');

	use SSilence\ImapClient\ImapClientException;
	use SSilence\ImapClient\ImapConnect;
	use SSilence\ImapClient\ImapClient;

	class ImapMailManager {
		const MOZILLA_ISP_DATABASE_URL = 'https://autoconfig.thunderbird.net/v1.1/%s';

		const AUTHENTICATION_TYPE_OAUTH2              = 'oauth2';
		const AUTHENTICATION_TYPE_PASSWORD_CLEAR_TEXT = 'password-cleartext';
		const AUTHENTICATION_TYPE_PASSWORD_ENCRYPTED  = 'password-encrypted';
		const AUTHENTICATION_TYPE_PLAIN               = 'plain';
		const AUTHENTICATION_TYPE_SECURE              = 'secure';

		const ENCRYPTION_METHOD = 'DES-EDE3-CBC';

		const PAGE_SIZE = 25;

		const SERVICE_IMAP = 'imap';

		const SECURITY_TYPE_PLAIN    = 'plain';
		const SECURITY_TYPE_SSL      = 'ssl';
		const SECURITY_TYPE_STARTTLS = 'starttls';

		const USER_NAME_TYPE_EMAIL_ADDRESS = '%emailaddress%';
		const USER_NAME_TYPE_LOCAL_PART    = '%emaillocalpart%';

		private $authenticationMethod;

		/** @var ImapClient */
		private $client;

		private $hostName;

		private $port;

		private $securityType;

		private $userNameType;

		private $currentMessageIndex = array ();
		private $messageUids         = array ();

		/**
		 * ImapMailManager constructor.
		 *
		 * @param string $hostName
		 * @param int $port
		 * @param string $securityType
		 * @param string $authenticationMethod
		 * @param string $userNameType
		 */
		public function __construct ($hostName, $port, $securityType, $authenticationMethod, $userNameType) {
			$this->validateConnectionArguments ($hostName, $port, $securityType, $authenticationMethod, $userNameType);
			$this->hostName             = $hostName;
			$this->port                 = $port;
			$this->securityType         = strtolower ($securityType);
			$this->authenticationMethod = strtolower ($authenticationMethod);
			$this->userNameType         = strtolower ($userNameType);
		}

		private function validateAuthenticationArguments ($emailAddress, $password) {
			if ((empty ($emailAddress)) || (filter_var ($emailAddress, FILTER_VALIDATE_EMAIL) === false)) {
				throw new Exception ('No se ha suministrado la dirección de correo electrónico o no es válida');
			} else if (empty ($password)) {
				throw new Exception ('No se ha suministrado la contraseña');
			}
		}

		/**
		 * @param string $hostName
		 * @param int $port
		 * @param string $securityType
		 * @param string $authenticationMethod
		 * @param string $userNameType
		 *
		 * @throws Exception
		 */
		private function validateConnectionArguments ($hostName, $port, $securityType, $authenticationMethod, $userNameType) {
			if (empty ($hostName)) {
				throw new Exception ('No se ha suministrado el nombre o la dirección IP del servidor IMAP');
			} else if ((empty ($port)) || (!is_numeric ($port)) || ($port < 1) || ($port > 65535)) {
				throw new Exception ('No se ha suministrado el puerto del servidor IMAP o no es un número válido');
			} else if ((empty ($securityType)) || (!in_array (strtolower ($securityType), array (self::SECURITY_TYPE_PLAIN, self::SECURITY_TYPE_SSL, self::SECURITY_TYPE_STARTTLS)))) {
				throw new Exception ('No se ha suministrado un mecanismo de seguridad válido (SSL, STARTTLS, PLAIN)');
			} else if ((empty ($authenticationMethod)) || (!in_array (strtolower ($authenticationMethod), array (self::AUTHENTICATION_TYPE_OAUTH2, self::AUTHENTICATION_TYPE_PASSWORD_CLEAR_TEXT, self::AUTHENTICATION_TYPE_PASSWORD_ENCRYPTED, self::AUTHENTICATION_TYPE_PLAIN, self::AUTHENTICATION_TYPE_SECURE)))) {
				throw new Exception ('No se ha suministrado un mecanismo de autenticación válido (OAuth2, PASSWORD-CLEARTEXT, PASSWORD-ENCRYPTED, PLAIN, SECURE)');
			} else if ((empty ($userNameType)) || (!in_array (strtolower ($userNameType), array (self::USER_NAME_TYPE_EMAIL_ADDRESS, self::USER_NAME_TYPE_LOCAL_PART)))) {
				throw new Exception ('No se ha suministrado un tipo de usuario válido (%EMAILADDRESS%, %EMAILLOCALPART%)');
			}
		}

		/**
		 * @return string[]|null
		 */
		public function getSubscribedFolders () {
			$serverFolders = $this->client->getFolders (null, 1);
			if (empty ($serverFolders)) {
				return null;
			}
			$folders = array ();
			foreach ($serverFolders as $serverFolder) {
				$folders [] = mb_convert_encoding ($serverFolder, 'UTF-8', 'UTF7-IMAP');
			}
			sort ($folders);
			return $folders;
		}

		/**
		 * @param string $emailAddress
		 * @param string $password
		 *
		 * @throws Exception
		 */
		public function login ($emailAddress, $password) {
			$this->validateAuthenticationArguments ($emailAddress, $password);
			$flags = array (
				'service'              => ImapConnect::SERVICE_IMAP,
				'validateCertificates' => ImapConnect::NOVALIDATE_CERT,
				'readonly'             => ImapConnect::READONLY,
			);
			if (strtolower ($this->securityType) == self::SECURITY_TYPE_STARTTLS) {
				$flags ['encrypt'] = ImapConnect::ENCRYPT_TLS;
			} else if (strtolower ($this->securityType) == self::SECURITY_TYPE_SSL) {
				$flags ['encrypt'] = ImapConnect::ENCRYPT_SSL;
			}

			if (strtolower ($this->authenticationMethod) == self::AUTHENTICATION_TYPE_SECURE) {
				$flags ['secure'] = ImapConnect::SECURE;
			} else {
				$flags ['secure'] = null;
			}

			if (strtolower ($this->userNameType) == self::USER_NAME_TYPE_EMAIL_ADDRESS) {
				$username = $emailAddress;
			} else {
				$username = substr ($emailAddress, 0, strpos ($emailAddress, '@'));
			}

			$this->client = new ImapClient (array (
				'flags'   => $flags,
				'mailbox' => array (
					'remote_system_name' => $this->hostName,
					'port'               => $this->port,
				),
				'connect' => array (
					'username' => $username,
					'password' => $password,
				),
			));
		}

		public function logout () {
			if (!$this->client) {
				return;
			}
			$this->client->close ();
			$this->currentMessageIndex = array ();
			$this->messageUids         = array ();
		}

		/**
		 * @param string $domain
		 *
		 * @return array|null
		 */
		private static function fetchMailSettingsFromMozillaIspDatabase ($domain) {
			$ch = curl_init ();
			curl_setopt ($ch, CURLOPT_FORBID_REUSE, true);
			curl_setopt ($ch, CURLOPT_FRESH_CONNECT, true);
			curl_setopt ($ch, CURLOPT_HEADER, false);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_URL, sprintf (self::MOZILLA_ISP_DATABASE_URL, $domain));
			$response = curl_exec ($ch);
			$httpcode = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
			curl_close ($ch);
			if ((intval ($httpcode) / 100) != 2) {
				return null;
			}

			/** @var object $xml */
			$xml = simplexml_load_string ($response);
			if ($xml === false) {
				return null;
			}

			$incomingServer = null;
			foreach ($xml->emailProvider->incomingServer as $incomingServer) {
				if (strtolower ($incomingServer ['type']) == self::SERVICE_IMAP) {
					$incomingServer = array (
						'protocol'             => self::SERVICE_IMAP,
						'hostname'             => strtolower ($incomingServer->hostname),
						'port'                 => intval ($incomingServer->port),
						'securitytype'         => strtolower ($incomingServer->socketType),
						'authenticationmethod' => strtolower ($incomingServer->authentication),
						'usernametype'         => strtolower ($incomingServer->username),
					);
					break;
				}
			}

			if (!empty ($incomingServer)) {
				$incomingServer ['domain'] = strtolower ($domain);
				return $incomingServer;
			} else {
				return null;
			}
		}

		/**
		 * @param string $domain
		 *
		 * @return array|null
		 */
		private static function guessHostNamesFromMxRecord ($domain) {
			if ((getmxrr ($domain, $mxRecords) === false) || (empty ($mxRecords))) {
				return null;
			}

			$hostNames = array ();
			$mxRecords = array_filter ($mxRecords);
			foreach ($mxRecords as $mxRecord) {
				$mxDomainParts = array_slice (explode ('.', $mxRecord), 1);
				while (count ($mxDomainParts) > 1) {
					$mxDomain     = join ('.', $mxDomainParts);
					$hostNames [] = "mail.{$mxDomain}";
					$hostNames [] = "imap.{$mxDomain}";
					$hostNames [] = "imap.mail.{$mxDomain}";
					$hostNames [] = "imap-mail.{$mxDomain}";
					array_shift ($mxDomainParts);
				}
			}
			return $hostNames;
		}

		/**
		 * @param string $hostName
		 * @param int $port
		 * @param string $protocol
		 *
		 * @return array|null
		 */
		private static function testImapSettings ($hostName, $port, $protocol = 'tcp') {
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
				$securityType = self::SECURITY_TYPE_STARTTLS;
			} else if ($protocol == self::SECURITY_TYPE_SSL) {
				$securityType = self::SECURITY_TYPE_SSL;
			} else {
				$securityType = self::SECURITY_TYPE_PLAIN;
			}

			return array (
				'protocol'             => self::SERVICE_IMAP,
				'hostname'             => strtolower ($hostName),
				'port'                 => intval ($port),
				'securitytype'         => $securityType,
				'authenticationmethod' => self::AUTHENTICATION_TYPE_PASSWORD_CLEAR_TEXT,
				'usernametype'         => self::USER_NAME_TYPE_EMAIL_ADDRESS,
			);
		}

		private function convertImapAddressToString ($address) {
			return (isset ($address->mailbox)) && (isset ($address->host)) ? "{$address->mailbox}@{$address->host}" : '';
		}

		private function convertImapAddressesToString ($addresses) {
			if (empty ($addresses)) {
				return '';
			}

			$addressesAsString = array ();
			foreach ($addresses as $address) {
				$addressesAsString [] = $this->convertImapAddressToString ($address);
			}
			return join (',', $addressesAsString);
		}

		private function convertToUtf8 ($s) {
			if (mb_detect_encoding ($s, 'UTF-8, ISO-8859-1, GBK') != 'UTF-8') {
				$s = utf8_encode ($s);
			}
			$s = iconv ('UTF-8', 'UTF-8//IGNORE', $s);
			return $s;
		}

		private function fetchMessageBody ($messageUid) {
			$body = $this->fetchMessageBodyPart ($messageUid, 'TEXT/HTML');
			if (empty ($body)) {
				$body = $this->fetchMessageBodyPart ($messageUid, 'TEXT/PLAIN');
			}
			return $this->convertToUtf8 ($body);
		}

		private function fetchMessageBodyPart ($messageUid, $mimeType, $structure = false, $partNumber = false) {
			if (!$structure) {
				$structure = imap_fetchstructure ($this->connection, $messageUid, FT_UID);
			}
			if (!$structure) {
				return false;
			}

			if ($mimeType == $this->getMimeType ($structure)) {
				$partNumber = max ($partNumber, 1);
				$text       = imap_fetchbody ($this->connection, $messageUid, $partNumber, (FT_UID | FT_PEEK));
				switch ($structure->encoding) {
					case 3:
						return imap_base64 ($text);
					case 4:
						return imap_qprint ($text);
					default:
						return $text;
				}
			}

			// multipart
			if ($structure->type != 1) {
				return false;
			}

			foreach ($structure->parts as $index => $subStruct) {
				$prefix   = $partNumber ? "{$partNumber}." : '';
				$nextPart = ($index + 1);
				$data     = $this->fetchMessageBodyPart ($messageUid, $mimeType, $subStruct, "{$prefix}{$nextPart}");
				if ($data) {
					return $data;
				}
			}
			return false;
		}

		private function getMimeType ($structure) {
			$primaryMimetype = array ('TEXT', 'MULTIPART', 'MESSAGE', 'APPLICATION', 'AUDIO', 'IMAGE', 'VIDEO', 'OTHER');
			return $structure->subtype ? "{$primaryMimetype [ (int) $structure->type ]}/{$structure->subtype}" : 'TEXT/PLAIN';
		}

		private function fetchMessageByUid ($messageUid) {
			$header = imap_headerinfo ($this->connection, imap_msgno ($this->connection, $messageUid));

			// get email data
			$subject = '';
			if ((isset ($header->subject)) && (strlen ($header->subject) > 0)) {
				foreach (imap_mime_header_decode ($header->subject) as $obj) {
					$subject .= $obj->text;
				}
			}

			return array (
				'uid'     => $messageUid,
				'to'      => $this->convertImapAddressesToString ($header->to),
				'cc'      => $this->convertImapAddressesToString ($header->cc),
				'from'    => $this->convertImapAddressToString ($header->from [0]),
				'date'    => date ('Y-m-d H:i:s', strtotime ($header->date)),
				'subject' => $this->convertToUtf8 ($subject),
				'body'    => $this->fetchMessageBody ($messageUid),
			);
		}

		private function init ($folderName, $lastUid = null) {
			if (empty ($folderName)) {
				$this->messageUids [ $folderName ]         = null;
				$this->currentMessageIndex [ $folderName ] = null;
				return;
			}

			imap_reopen ($this->connection, "{{$this->connectionString}}{$folderName}");
			$messageUids = imap_sort ($this->connection, SORTARRIVAL, 1, (SE_UID | SE_NOPREFETCH));
			if (empty ($messageUids)) {
				$this->messageUids [ $folderName ]         = null;
				$this->currentMessageIndex [ $folderName ] = null;
				return;
			}

			$this->messageUids [ $folderName ] = array ();
			foreach ($messageUids as $messageUid) {
				if ((!empty ($lastUid)) && ($lastUid >= $messageUid)) {
					continue;
				}
				$this->messageUids [ $folderName ][] = $messageUid;
			}
			$this->currentMessageIndex [ $folderName ] = !empty ($this->messageUids [ $folderName ]) ? 0 : null;
		}

		private static function generateRandomBytes ($length) {
			// Use PHP7 true random generator
			if (function_exists ('random_bytes')) {
				// random_bytes() can throw an Error/TypeError/Exception in some cases
				try {
					$random = random_bytes ($length);
				} catch (Throwable $ignored) {
					$random = null;
				}
			} else {
				$random = null;
			}

			if (!$random) {
				$random = openssl_random_pseudo_bytes ($length);
			}

			return $random;
		}

		public function fetchNextMessage ($folderName, $lastUid = null) {
			if (!isset ($this->messageUids [ $folderName ])) {
				$this->init ($folderName, $lastUid);
			}

			if (($this->messageUids [ $folderName ] === null) || ($this->currentMessageIndex [ $folderName ] === null) || (!isset ($this->messageUids [ $folderName ][ $this->currentMessageIndex [ $folderName ] ]))) {
				return null;
			}

			$currentHeaderIndex = $this->currentMessageIndex [ $folderName ];
			$currentMessageUid  = $this->messageUids [ $folderName ][ $currentHeaderIndex ];
			$currentMessage     = $this->fetchMessageByUid ($currentMessageUid);
			$this->currentMessageIndex [ $folderName ]++;
			return $currentMessage;
		}

		/**
		 * @param string $emailAddress
		 *
		 * @return array|null
		 */
		public static function detectMailServerSettings ($emailAddress) {
			if ((empty ($emailAddress)) || (filter_var ($emailAddress, FILTER_VALIDATE_EMAIL) === false)) {
				return null;
			}

			$domain = strtolower (substr ($emailAddress, (strpos ($emailAddress, '@') + 1)));

			// Buscar en Mozilla ISP database
			$settings = self::fetchMailSettingsFromMozillaIspDatabase ($domain);
			if (!empty ($settings)) {
				$settings ['domain'] = $domain;
				return $settings;
			}

			// Adivinar
			$mxRecordsHostNames = self::guessHostNamesFromMxRecord ($domain);
			$hostNames          = array_unique (array_merge (
				array ("mail.{$domain}", "imap.{$domain}", "imap.mail.{$domain}", "imap-mail.{$domain}"),
				isset ($mxRecordsHostNames) ? $mxRecordsHostNames : array ()
			));

			$settings = null;
			foreach ($hostNames as $hostName) {
				$settings = self::testImapSettings ($hostName, 143);
				if ((!empty ($settings)) && ($settings ['securitytype'] == self::SECURITY_TYPE_STARTTLS)) {
					break;
				}

				$settings = self::testImapSettings ($hostName, 993, self::SECURITY_TYPE_SSL);
				if (!empty ($settings)) {
					break;
				}
			}

			if (!empty ($settings)) {
				$settings ['domain'] = $domain;
				return $settings;
			} else {
				return null;
			}
		}

		public static function decryptPassword ($encryptedPassword, $key) {
			$cipher  = base64_decode ($encryptedPassword);
			$options = defined ('OPENSSL_RAW_DATA') ? OPENSSL_RAW_DATA : true;
			$ivSize  = openssl_cipher_iv_length (self::ENCRYPTION_METHOD);
			$iv      = substr ($cipher, 0, $ivSize);
			// session corruption? (#1485970)
			if (strlen ($iv) < $ivSize) {
				return '';
			}
			$cipher = substr ($cipher, $ivSize);
			return openssl_decrypt ($cipher, self::ENCRYPTION_METHOD, $key, $options, $iv);
		}

		public static function encryptPassword ($unencryptedPassword, $key) {
			if (empty ($unencryptedPassword)) {
				return '';
			}

			$options = defined ('OPENSSL_RAW_DATA') ? OPENSSL_RAW_DATA : true;
			$iv      = self::generateRandomBytes (openssl_cipher_iv_length (self::ENCRYPTION_METHOD));
			return base64_encode ($iv . openssl_encrypt ($unencryptedPassword, self::ENCRYPTION_METHOD, $key, $options, $iv));
		}

	}
