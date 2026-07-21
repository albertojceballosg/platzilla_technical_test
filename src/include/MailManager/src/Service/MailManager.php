<?php
	namespace Platzilla\MailManager\Service;

	use League\OAuth2\Client\Token\AccessTokenInterface;
	use Platzilla\MailManager\Account\GenericAccount;
	use Platzilla\MailManager\Connection\Imap as ImapConnection;
	use Platzilla\MailManager\Connection\Pop3 as Pop3Connection;
	use Platzilla\MailManager\Connection\Smtp as SmtpConnection;
	use Platzilla\MailManager\MailException;
	use Platzilla\MailManager\Message\Message;
	use Platzilla\MailManager\Provider\GenericProvider;
	use Platzilla\MailManager\Type\AuthenticationMethod;
	use Platzilla\MailManager\Type\SecurityType;
	use Platzilla\MailManager\Type\ServiceType;
	use Platzilla\MailManager\Type\UserNameType;
	use Platzilla\MailManager\Utils\MailUtils;

	class MailManager {
		const PAGE_SIZE = 25;

		private $currentMessageIndex = array ();
		private $messageUids         = array ();

		/**
		 * @param GenericAccount $account
		 *
		 * @return string
		 * @throws MailException
		 * @throws MailManagerException
		 */
		private function getOauth2AccessToken ($account) {
			$token = $account->getAccessToken ();
			if (!($token instanceof AccessTokenInterface)) {
				throw new MailManagerException (MailManagerException::UNABLE_TO_GET_OAUTH2_TOKEN);
			}

			if ($token->hasExpired ()) {
				throw new MailManagerException (MailManagerException::TOKEN_EXPIRED);
			}

			return $token->getToken ();
		}

		/**
		 * @param string $emailAddress
		 * @param string $accessToken
		 * @param GenericProvider $provider
		 *
		 * @return ImapConnection|Pop3Connection
		 * @throws MailManagerException
		 */
		private function loginIncomingServer ($emailAddress, $accessToken, $provider) {
			if (!($provider instanceof GenericProvider)) {
				throw new MailManagerException (MailManagerException::INVALID_PROVIDER);
			}

			$userNameType = $provider->getIncomingUserNameType ();
			if ($userNameType == UserNameType::LOCAL_PART) {
				$userName = substr ($emailAddress, 0, (strpos ($emailAddress, '@')));
			} else {
				$userName = $emailAddress;
			}

			$securityType = $provider->getIncomingSecurityType ();
			if ($securityType == SecurityType::SSL) {
				$ssl = 'ssl';
			} else if ($securityType == SecurityType::STARTTLS) {
				$ssl = 'tls';
			} else {
				$ssl = null;
			}

			$service = $provider->getIncomingService ();
			$isOauth = ($provider->getIncomingAuthenticationMethod () == AuthenticationMethod::OAUTH2);
			if ($service == ServiceType::IMAP) {
				$connection = new ImapConnection ($provider->getIncomingHostName (), $provider->getIncomingPort (), $ssl, $isOauth);
			} else {
				$connection = new Pop3Connection ($provider->getIncomingHostName (), $provider->getIncomingPort (), $ssl, $isOauth);
			}
			if (!$connection->login ($userName, $accessToken)) {
				throw new MailManagerException (sprintf ('%s: %s en el puerto %s', MailManagerException::UNABLE_TO_CONNECT, $provider->getIncomingHostName (), $provider->getIncomingPort ()));
			}
			return $connection;
		}

		/**
		 * @param string $emailAddress
		 * @param string $accessToken
		 * @param GenericProvider $provider
		 * @param string $localHostName
		 *
		 * @return SmtpConnection
		 * @throws MailManagerException
		 */
		private function loginOutgoingServer ($emailAddress, $accessToken, $provider, $localHostName) {
			if (!($provider instanceof GenericProvider)) {
				throw new MailManagerException (MailManagerException::INVALID_PROVIDER);
			}

			$userNameType = $provider->getOutgoingUserNameType ();
			if ($userNameType == UserNameType::LOCAL_PART) {
				$userName = substr ($emailAddress, 0, (strpos ($emailAddress, '@')));
			} else {
				$userName = $emailAddress;
			}

			$securityType = $provider->getOutgoingSecurityType ();
			if ($securityType == SecurityType::SSL) {
				$ssl = 'ssl';
			} else if ($securityType == SecurityType::STARTTLS) {
				$ssl = 'tls';
			} else {
				$ssl = null;
			}

			$isOauth    = ($provider->getOutgoingAuthenticationMethod () == AuthenticationMethod::OAUTH2);
			$connection = new SmtpConnection ($provider->getOutgoingHostName (), $provider->getOutgoingPort (), $ssl, $isOauth);
			if (!$connection->login ($userName, $accessToken, $localHostName)) {
				throw new MailManagerException (sprintf ('%s: %s en el puerto %s', MailManagerException::UNABLE_TO_CONNECT, $provider->getOutgoingHostName (), $provider->getOutgoingPort ()));
			}
			return $connection;
		}

		/**
		 * @param ImapConnection|Pop3Connection|SmtpConnection $connection
		 */
		private function logout ($connection) {
			if (($connection instanceof ImapConnection) || ($connection instanceof Pop3Connection) || ($connection instanceof SmtpConnection)) {
				$connection->logout ();
			}
			$this->currentMessageIndex = array ();
			$this->messageUids         = array ();
		}

		/**
		 * @param GenericAccount $account
		 * @param integer $startIncomingMessageUid
		 * @param integer $startOutgoingMessageUid
		 * @param string $encryptionKey
		 * @param integer $maximumMessages
		 * @param string $mask
		 *
		 * @return array
		 * @throws \Exception
		 */
		public function fetchMessages ($account, $startIncomingMessageUid, $startOutgoingMessageUid, $encryptionKey, $maximumMessages = 0, $mask = null) {
			$connection = null;
			try {
				$provider = $account->getProvider ();
				if ($provider->getIncomingAuthenticationMethod () == AuthenticationMethod::OAUTH2) {
					$accessToken = $account->getAccessToken ()->getToken ();
				} else {
					$accessToken = MailUtils::decrypt ($account->getPassword (), $encryptionKey);
				}
				$connection      = $this->loginIncomingServer ($account->getEmailAddress (), $accessToken, $provider);
				if ($provider->getIncomingService () == ServiceType::POP3) {
					$incomingMessages = $connection->fetchMessages ($account->getEmailAddress (), $startIncomingMessageUid, $maximumMessages);
					$outgoingMessages = null;
				} else {
					$incomingMessages = $connection->fetchMessages ($account->getEmailAddress (), $startIncomingMessageUid, $maximumMessages, $account->getIncomingFolderName (), $mask);
					$outgoingMessages = $connection->fetchMessages ($account->getEmailAddress (), $startOutgoingMessageUid, $maximumMessages, $account->getOutgoingFolderName (), $mask);
				}
				$messages = array (
					'incoming' => $incomingMessages,
					'outgoing' => $outgoingMessages,
				);
			} catch (\Exception $ie) {
				$messages = array (
					'incoming' => null,
					'outgoing' => null,
				);
				$e        = $ie;
			}
			$this->logout ($connection);
			if (isset ($e)) {
				throw $e;
			}
			return $messages;
		}

		/**
		 * @param string $emailAddress
		 * @param string $accessToken
		 * @param GenericProvider $provider
		 *
		 * @return null|string[]
		 * @throws \Exception
		 */
		public function getSubscribedFolders ($emailAddress, $accessToken, $provider) {
			$connection  = null;
			$folderNames = null;
			try {
				$connection  = $this->loginIncomingServer ($emailAddress, $accessToken, $provider);
				$folders     = $connection->listMailbox ();
				$folderNames = array_keys ($folders);
				sort ($folderNames);
			} catch (\Exception $ie) {
				$e = $ie;
			}
			$this->logout ($connection);
			if (isset ($e)) {
				throw $e;
			}

			return $folderNames;
		}

		/**
		 * @param GenericAccount $account
		 * @param Message $message
		 * @param string $encryptionKey
		 * @param string $localHostName
		 *
		 * @throws MailException
		 */
		public function sendMessage ($account, $message, $encryptionKey, $localHostName) {
			$connection = null;
			try {
				$provider             = $account->getProvider ();
				$authenticationMethod = $provider->getOutgoingAuthenticationMethod ();
				if ($authenticationMethod == AuthenticationMethod::OAUTH2) {
					$accessToken = $account->getAccessToken ()->getToken ();
				} else {
					$accessToken = MailUtils::decrypt ($account->getPassword (), $encryptionKey);
				}
				$connection = $this->loginOutgoingServer ($account->getEmailAddress (), $accessToken, $provider, $localHostName);
				$connection->sendMessage ($account->getEmailAddress (), $message);
				$this->logout ($connection);

				$authenticationMethod = $provider->getIncomingAuthenticationMethod ();
				if ($authenticationMethod == AuthenticationMethod::OAUTH2) {
					$accessToken = $account->getAccessToken ()->getToken ();
				} else {
					$accessToken = MailUtils::decrypt ($account->getPassword (), $encryptionKey);
				}
				$connection = $this->loginIncomingServer ($account->getEmailAddress (), $accessToken, $provider);
				$connection->saveSentMessage ($account->getEmailAddress (), $account->getOutgoingFolderName (), $message);
			} catch (MailException $ie) {
				$e = $ie;
			}
			$this->logout ($connection);
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param GenericAccount $account
		 * @param string $encryptionKey
		 * @param string $localHostName
		 *
		 * @throws MailManagerException
		 * @throws \Exception
		 * @throws \Platzilla\MailManager\Account\GenericAccountException
		 */
		public function testAccount ($account, $encryptionKey, $localHostName) {
			if (!($account instanceof GenericAccount)) {
				throw new MailManagerException (MailManagerException::INVALID_ACCOUNT);
			} else {
				$account->validate ();
			}

			$provider             = $account->getProvider ();
			$authenticationMethod = $provider->getIncomingAuthenticationMethod ();
			if ($authenticationMethod == AuthenticationMethod::OAUTH2) {
				$accessToken = $this->getOauth2AccessToken ($account);
			} else {
				$accessToken = MailUtils::decrypt ($account->getPassword (), $encryptionKey);
			}

			$connection = null;
			try {
				$connection = $this->loginIncomingServer ($account->getEmailAddress (), $accessToken, $provider);
			} catch (\Exception $ie) {
				$e = $ie;
			}
			$this->logout ($connection);
			if (isset ($e)) {
				throw $e;
			}

			$authenticationMethod = $provider->getOutgoingAuthenticationMethod ();
			if ($authenticationMethod == AuthenticationMethod::OAUTH2) {
				$accessToken = $this->getOauth2AccessToken ($account);
			} else {
				$accessToken = MailUtils::decrypt ($account->getPassword (), $encryptionKey);
			}

			$connection = null;
			try {
				$connection = $this->loginOutgoingServer ($account->getEmailAddress (), $accessToken, $provider, $localHostName);
			} catch (\Exception $ie) {
				$e = $ie;
			}
			$this->logout ($connection);
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @return MailManager
		 */
		public static function getInstance () {
			return new self ();
		}

	}
