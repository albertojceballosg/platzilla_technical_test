<?php
	namespace Platzilla\MailManager\Connection;

	use PhpMimeMailParser\Parser as PhpMimeMailParser;
	use Platzilla\MailManager\Message\Attachment;
	use Platzilla\MailManager\Message\Message;
	use Platzilla\MailManager\Type\ServiceType;
	use Zend\Mail\Protocol\Exception\ExceptionInterface;
	use Zend\Mail\Protocol\Exception\RuntimeException;
	use Zend\Stdlib\ErrorHandler;
	use Zend\Mail\Protocol\Pop3 as BasePop3;

	class Pop3 extends BasePop3 {
		/** @var boolean */
		private $isOauth;

		/**
		 * Pop3 constructor.
		 *
		 * @param string $host
		 * @param integer|null $port
		 * @param string|boolean $ssl
		 * @param boolean $isOauth
		 */
		public function __construct ($host = '', $port = null, $ssl = false, $isOauth = false) {
			parent::__construct ($host, $port, $ssl);
			$this->isOauth = $isOauth;
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
				$socket = fsockopen ($hostName, $port, $errno, $errstr, self::TIMEOUT_CONNECTION);
			}
			$error = ErrorHandler::stop ();
			if (!$socket) {
				throw new RuntimeException (sprintf ('cannot connect to host %s', ($error ? sprintf ('; error = %s (errno = %d )', $error->getMessage (), $error->getCode ()) : '')), 0, $error);
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
					$port = ServiceType::POP3_SSL_PORT;
					break;
				case 'tls':
					$port = ServiceType::POP3_DEFAULT_PORT;
					break;
				default:
					$port = ServiceType::POP3_DEFAULT_PORT;
					break;
			}
			return $port;
		}

		/**
		 * @param PhpMimeMailParser $parser
		 * @param string $headerName
		 * @param string|null $emptyValue
		 *
		 * @return string|null
		 */
		private function getHeader ($parser, $headerName, $emptyValue = null) {
			$dummy = $parser->getHeader ($headerName);
			return $dummy !== false ? $dummy : $emptyValue;
		}

		/**
		 * @param string $welcome
		 *
		 * @return null|string
		 */
		private function getTimeStamp ($welcome) {
			strtok ($welcome, '<');
			$timeStamp = strtok ('>');
			if (!strpos ($timeStamp, '@')) {
				$timeStamp = null;
			} else {
				$timeStamp = "<{$timeStamp}>";
			}
			return $timeStamp;
		}

		/**
		 * @return string
		 * @throws RuntimeException
		 */
		private function readLine () {
			$line = fgets ($this->socket);
			if ($line === false) {
				throw new RuntimeException ('cannot read - connection closed?');
			}

			return trim ($line);
		}

		/**
		 * @param string $user
		 * @param string $accessToken
		 *
		 * @return boolean
		 */
		private function sendOauth2Authentication ($user, $accessToken) {
			$this->sendRequest (sprintf ('AUTH XOAUTH2 %s', base64_encode ("user={$user}\1auth=Bearer {$accessToken}\1\1")));
			$success = false;
			while (true) {
				$response = $this->readLine ();
				$isPlus   = ($response == '+');
				if ($isPlus) {
					$this->sendRequest ('');
				} else if (preg_match ('/^-ERR /i', $response)) {
					break;
				} else if (preg_match ('/^\+OK /i', $response)) {
					$success = true;
					break;
				}
			}
			return $success;
		}

		/**
		 * @param boolean $isTls
		 *
		 * @throws RuntimeException
		 */
		private function startTls ($isTls) {
			if (!$isTls) {
				return;
			}

			$this->request ('STLS');
			$result = stream_socket_enable_crypto ($this->socket, true, $this->getCryptoMethod ());
			if (!$result) {
				throw new RuntimeException ('cannot enable TLS');
			}
		}

		/**
		 * Open connection to POP3 server
		 *
		 * @param string $hostName Hostname or IP address of POP3 server
		 * @param integer|null $port Port of POP3 server, default is 110 (995 for ssl)
		 * @param string|boolean $ssl Use 'SSL', 'TLS' or false
		 *
		 * @throws RuntimeException
		 * @return string Welcome message
		 */
		public function connect ($hostName, $port = null, $ssl = false) {
			$ssl             = !empty ($ssl) ? strtolower ($ssl) : false;
			$hostName        = $ssl == 'ssl' ? "ssl://{$hostName}" : $hostName;
			$port            = $this->fixEmptyPort ($port, $ssl);
			$this->socket    = $this->createSocket ($hostName, $port, $ssl);
			$welcome         = $this->readResponse ();
			$this->timestamp = $this->getTimeStamp ($welcome);
			$this->startTls ($ssl == 'tls');

			return $welcome;
		}

		/**
		 * @param string $accountHolder
		 * @param integer $startUid
		 * @param integer $maximumMessages
		 *
		 * @return null|\Platzilla\MailManager\Message\Message[]
		 */
		public function fetchMessages ($accountHolder, $startUid, $maximumMessages) {
			$response = $this->getList ();
			if ((empty ($response)) || (!is_array ($response))) {
				return null;
			}

			$messageIds = array_keys ($response);

			$totalFetchedMesages = 0;
			$parser              = new PhpMimeMailParser ();
			$messages            = array ();
			foreach ($messageIds as $messageId) {
				if (($maximumMessages > 0) && ($totalFetchedMesages >= $maximumMessages)) {
					break;
				}

				$messageUid = hexdec ($this->uniqueid ($messageId));
				if ($messageUid > $startUid) {
					$rawContents = $this->retrieve ($messageId);
					$parser->setText ($rawContents);

					$htmlMessageBody = $parser->getMessageBody ('htmlEmbedded');
					$message         = Message::getInstance ($accountHolder)
						->setBcc ($this->getHeader ($parser, 'bcc'))
						->setBody ($htmlMessageBody ? $htmlMessageBody : $parser->getMessageBody ())
						->setCc ($this->getHeader ($parser, 'cc'))
						->setDate (date_create ($parser->getHeader ('date')))
						->setFolderName ('Entrantes')
						->setFrom ($this->getHeader ($parser, 'from'))
						->setSubject ($this->getHeader ($parser, 'subject'))
						->setTo ($this->getHeader ($parser, 'to'))
						->setUid ($messageUid)
						->addAccountHolderToBcc ();

					$messageAttachments = array ();
					$attachments        = $parser->getAttachments (false);
					if (count ($attachments) > 0) {
						foreach ($attachments as $attachment) {
							$messageAttachments [] = Attachment::getInstance ()
								->setData ($attachment->getContent ())
								->setFileName ($attachment->getFilename ())
								->setMimeType ($attachment->getContentType ());
						}
					} else {
						$messageAttachments = null;
					}
					$message->setAttachments ($messageAttachments);
					$messages [] = $message;
					$totalFetchedMesages++;
				}
			}
			return count ($messages) > 0 ? $messages : null;
		}

		/**
		 * @return boolean
		 */
		public function isConnected () {
			return !empty ($this->socket);
		}

		/**
		 * @return string[]
		 */
		public function listMailbox () {
			return array (
				'Entrantes' => 'Entrantes',
				'Salientes' => 'Salientes',
			);
		}

		/**
		 * @param string $user
		 * @param string $password
		 *
		 * @return boolean
		 */
		public function login ($user, $password) {
			if ($this->isOauth) {
				return $this->sendOauth2Authentication ($user, $password);
			} else {
				try {
					$this->request (sprintf ('APOP %s %s', $user, md5 ($this->timestamp . $password)));
					return true;
				} catch (ExceptionInterface $ignored) {
					// ignore
				}

				$this->sendRequest ("USER {$user}");
				$response = $this->readLine ();
				if (strpos ($response, '+OK') !== 0) {
					return false;
				}

				$this->sendRequest ("PASS {$password}");
				$response = $this->readLine ();
				if (strpos ($response, '+OK') !== 0) {
					return false;
				}

				return true;
			}
		}

		public function saveSentMessage () {
			// POP3 no tiene sistema de carpetas
		}

	}
