<?php
	namespace Platzilla\MailManager\Connection;

	require_once (__DIR__ . '/../../vendor/pear-pear.php.net/Mail_Mime/Mail/mime.php');

	use Platzilla\MailManager\Message\Message;
	use Platzilla\MailManager\Message\MessageException;
	use Platzilla\MailManager\Type\ServiceType;
	use Zend\Mail\Message as ZendMessage;
	use Zend\Mail\Protocol\Exception\RuntimeException;
	use Zend\Mail\Protocol\Smtp as BaseSmtp;
	use Zend\Mail\Transport\Smtp as SmtpTransport;
	use Zend\Stdlib\ErrorHandler;

	class Smtp extends BaseSmtp {
		/** @var string */
		private $accessToken = null;

		/** @var boolean */
		private $isOauth;

		/** @var string */
		private $userName;

		/**
		 * Smtp constructor.
		 *
		 * @param string $host
		 * @param integer $port
		 * @param array|null $ssl
		 * @param boolean $isOauth
		 */
		public function __construct ($host, $port, $ssl, $isOauth = false) {
			$configuration = array ();
			if (!empty ($ssl)) {
				$configuration ['ssl'] = $ssl;
			}
			$this->isOauth = $isOauth;
			parent::__construct ($host, $port, $configuration);
			$this->connect ();
		}

		/**
		 * @param string $hostName
		 * @param integer $port
		 * @param string|boolean $ssl
		 *
		 * @return resource
		 * @throws RuntimeException
		 */
		private function createSocket ($hostName, $port, $ssl) {
			ErrorHandler::start ();
			if (!empty ($ssl)) {
				$context = stream_context_create (array ('ssl' => array ('verify_peer_name' => false, 'verify_peer' => false, 'allow_self_signed' => true)));
				$socket  = stream_socket_client ("{$hostName}:{$port}", $errno, $errstr, self::TIMEOUT_CONNECTION, STREAM_CLIENT_CONNECT, $context);
			} else {
				$socket = stream_socket_client ("{$hostName}:{$this->port}", $errno, $errstr, self::TIMEOUT_CONNECTION);
			}
			$error = ErrorHandler::stop ();
			if (!$socket) {
				throw new RuntimeException (sprintf ('cannot connect to host %s', ($error ? sprintf ('; error = %s (errno = %d )', $error->getMessage (), $error->getCode ()) : '')), 0, $error);
			} else if (stream_set_timeout ($socket, self::TIMEOUT_CONNECTION) === false) {
				throw new RuntimeException ('Could not set stream timeout');
			}

			return $socket;
		}

		/**
		 * @param integer $port
		 * @param string $ssl
		 *
		 * @return integer
		 */
		private function fixEmptyPort ($port, $ssl) {
			if ($port) {
				return $port;
			}

			switch ($ssl) {
				case 'ssl':
					$port = ServiceType::SMTP_SSL_PORT;
					break;
				case 'tls':
					$port = ServiceType::SMTP_STARTTLS_PORT;
					break;
				default:
					$port = ServiceType::SMTP_DEFAULT_PORT;
					break;
			}
			return $port;
		}

		/**
		 * @param string $accountHolder
		 * @param Message $message
		 *
		 * @return string
		 */
		private function getRawMessageBody ($accountHolder, $message) {
			list ($body, $inlineFilesData) = $this->splitBodyContents ($message->getBody (), strtolower (substr ($accountHolder, (strpos ($accountHolder, '@') + 1))));

			$arguments = array (
				'head_charset' => 'UTF-8',
				'html_charset' => 'UTF-8',
				'text_charset' => 'UTF-8'
			);
			$mimeMail = new \Mail_mime ($arguments);
			$mimeMail->setHTMLBody ($body);
			if (!empty ($inlineFilesData)) {
				foreach ($inlineFilesData as $inlineFileName => $inlineFileData) {
					$mimeMail->addHTMLImage ($inlineFileData [1], $inlineFileData [0], $inlineFileName, false, $inlineFileName);
				}
			}

			$attachments = $message->getAttachments ();
			if (!empty ($attachments)) {
				foreach ($attachments as $attachment) {
					$mimeMail->addAttachment ($attachment->getData (), $attachment->getMimeType (), $attachment->getFileName (), false);
				}
			}

			return $mimeMail->getMessage ();
		}

		/**
		 * @param string $oldRecipient
		 *
		 * @return string[]|null
		 * @throws MessageException
		 */
		private function normalizeRecipients ($oldRecipient) {
			if (empty ($oldRecipient)) {
				return null;
			}

			$dummies = explode (',', $oldRecipient);
			if (empty ($dummies)) {
				return null;
			}

			$recipients = array ();
			foreach ($dummies as $dummy) {
				$dummy = str_replace ('>', '', str_replace ('<', '', array_pop (explode (' ', trim ($dummy)))));
				if (filter_var ($dummy, FILTER_VALIDATE_EMAIL) === false) {
					throw new MessageException (sprintf ('%s: %s', MessageException::INVALID_RECIPIENT, $dummy));
				}

				$recipients [] = $dummy;
			}

			return $recipients;
		}

		/**
		 * @param string $userName
		 * @param string $accessToken
		 */
		private function sendLoginAuthentication ($userName, $accessToken) {
			$this->_send ('AUTH LOGIN');
			$this->_expect (334);
			$this->_send (base64_encode ($userName));
			$this->_expect (334);
			$this->_send (base64_encode ($accessToken));
			$this->_expect (235);
		}

		/**
		 * @param string $userName
		 * @param string $accessToken
		 */
		private function sendOauth2Authentication ($userName, $accessToken) {
			$this->_send (sprintf ('AUTH XOAUTH2 %s', base64_encode ("user={$userName}\1auth=Bearer {$accessToken}\1\1")));
			$this->_expect (235, 180);
		}

		/**
		 * @param string $userName
		 * @param string $plainPassword
		 */
		private function sendPlainAuthentication ($userName, $plainPassword) {
			$this->_send ('AUTH PLAIN');
			$this->_expect (334);
			$this->_send (base64_encode ("\0{$userName}\0{$plainPassword}"));
			$this->_expect (235);
		}

		/**
		 * @param string $messageBody
		 * @param string $domain
		 *
		 * @return array
		 */
		private function splitBodyContents ($messageBody, $domain) {
			$inlineFilesData = array ();
			$inlineDataStart = strpos ($messageBody, '"data:');
			while ($inlineDataStart !== false) {
				$inlineDataEnd = strpos ($messageBody, '"', ($inlineDataStart + 1));
				$inlineData    = substr ($messageBody, ($inlineDataStart + 1), ($inlineDataEnd - $inlineDataStart - 1));
				$inlineId      = sprintf ('%s@%s', uniqid (time (), true), $domain);
				$messageBody   = str_replace ($inlineData, "cid:{$inlineId}", $messageBody);

				$mimeTypeStart                 = (strpos ($inlineData, 'data:') + 5);
				$encodedDataStart              = strpos ($inlineData, ';base64');
				$mimeType                      = substr ($inlineData, $mimeTypeStart, ($encodedDataStart - $mimeTypeStart));
				$fileData                      = base64_decode (substr ($inlineData, ($encodedDataStart + 8)));
				$inlineFilesData [ $inlineId ] = array ($mimeType, $fileData);
				$inlineDataStart               = strpos ($messageBody, '"data:');
			}

			return array ($messageBody, $inlineFilesData);
		}

		/**
		 * Perform PLAIN authentication with supplied credentials
		 */
		public function auth () {
			// Ensure AUTH has not already been initiated.
			parent::auth ();

			if ($this->isOauth) {
				$this->sendOauth2Authentication ($this->userName, $this->accessToken);
			} else {
				try {
					$this->sendPlainAuthentication ($this->userName, $this->accessToken);
				} catch (RuntimeException $ignored) {
					$this->sendLoginAuthentication ($this->userName, $this->accessToken);
				}
			}
			$this->auth = true;
		}

		/**
		 * @return boolean
		 * @throws RuntimeException
		 */
		public function connect () {
			$hostName     = $this->secure == 'ssl' ? "ssl://{$this->host}" : $this->host;
			$this->port   = $this->fixEmptyPort ($this->port, $this->secure);
			$this->socket = $this->createSocket ($hostName, $this->port, $this->secure);
			return true;
		}

		/**
		 * @return boolean
		 */
		public function isConnected () {
			return $this->sess;
		}

		/**
		 * @param string $userName
		 * @param string $accessToken
		 * @param string $hostName
		 *
		 * @return boolean
		 */
		public function login ($userName, $accessToken, $hostName) {
			$this->accessToken = $accessToken;
			$this->userName    = $userName;

			try {
				$this->helo ($hostName);
				$result = true;
			} catch (\Exception $ignored) {
				$result = false;
			}
			return $result;
		}

		public function logout () {
			parent::disconnect ();
		}

		/**
		 * @param string $accountHolder
		 * @param Message $message
		 */
		public function sendMessage ($accountHolder, $message) {
			$to      = $this->normalizeRecipients ($message->getTo ());
			$cc      = $this->normalizeRecipients ($message->getCc ());
			$bcc     = $this->normalizeRecipients ($message->getBcc ());
			$subject = $message->getSubject ();
			$rawBody = $this->getRawMessageBody ($accountHolder, $message);

			$zendMessage = ZendMessage::fromString ($rawBody)
				->setFrom ($accountHolder)
				->setSubject ($subject);
			if (!empty ($to)) {
				$zendMessage->setTo ($to);
			}
			if (!empty ($cc)) {
				$zendMessage->setCc ($cc);
			}
			if (!empty ($bcc)) {
				$zendMessage->setBcc ($bcc);
			}

			$transport = new SmtpTransport ();
			$transport->setConnection ($this);
			$transport->send ($zendMessage);
		}

	}
