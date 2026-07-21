<?php
	namespace Platzilla\MailManager\Connection;

	use PhpMimeMailParser\Parser as PhpMimeMailParser;
	use Platzilla\MailManager\Message\Attachment;
	use Platzilla\MailManager\Message\Message;
	use Platzilla\MailManager\Message\MessageException;
	use Platzilla\MailManager\Type\ServiceType;
	use Platzilla\MailManager\Utils\MailUtils;
	use Zend\Mail\Protocol\Exception\RuntimeException;
	use Zend\Mail\Protocol\Imap as BaseImap;
	use Zend\Mail\Storage;
	use Zend\Stdlib\ErrorHandler;

	class Imap extends BaseImap {
		/** @var boolean */
		private $isOauth;

		/**
		 * ImapConnection constructor.
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
		 * @param integer $messageUid
		 *
		 * @return string|null
		 */
		private function fetchMessageRawContents ($messageUid) {
			$response = $this->requestAndResponse ("UID FETCH {$messageUid} BODY.PEEK[]");
			if ((empty ($response)) || (!is_array ($response))) {
				return null;
			}

			$contents = '';
			foreach ($response [0][2] as $index => $value) {
				if ($value == 'BODY[]') {
					$contents = $response [0][2][ ($index + 1) ];
					break;
				}
			}

			return $contents;
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
					$port = ServiceType::IMAP_SSL_PORT;
					break;
				case 'tls':
					$port = ServiceType::IMAP_DEFAULT_PORT;
					break;
				default:
					$port = ServiceType::IMAP_DEFAULT_PORT;
					break;
			}
			return $port;
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
		 * @return string[]|null
		 */
		private function fetchMessageUids () {
			$response = $this->requestAndResponse ('UID SEARCH ALL');
			if ((empty ($response)) || (!is_array ($response))) {
				return null;
			}

			$messageUids = array_shift ($response);
			if ($messageUids [0] != 'SEARCH') {
				return null;
			}

			array_shift ($messageUids);
			return $messageUids;
		}

		/**
		 * @param string $user
		 * @param string $accessToken
		 *
		 * @return boolean
		 */
		private function sendOauth2Authentication ($user, $accessToken) {
			$authenticateParams = array ('XOAUTH2', base64_encode ("user={$user}\1auth=Bearer {$accessToken}\1\1"));
			$this->sendRequest ('AUTHENTICATE', $authenticateParams);
			$success = false;
			while (true) {
				$response = '';
				$isPlus   = $this->readLine ($response, '+', true);
				if ($isPlus) {
					$this->sendRequest ('');
				} else if ((preg_match ('/^NO /i', $response)) || (preg_match ('/^BAD /i', $response))) {
					break;
				} else if (preg_match ('/^OK /i', $response)) {
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

			$result = $this->requestAndResponse ('STARTTLS');
			$result = $result && stream_socket_enable_crypto ($this->socket, true, $this->getCryptoMethod ());
			if (!$result) {
				throw new RuntimeException ('cannot enable TLS');
			}
		}

		/**
		 * Open connection to IMAP server
		 *
		 * @param string $hostName Hostname or IP address of IMAP server
		 * @param integer|null $port Port of IMAP server, default is 143 (993 for ssl)
		 * @param string|boolean $ssl Use 'SSL', 'TLS' or false
		 *
		 * @return void
		 * @throws RuntimeException
		 */
		public function connect ($hostName, $port = null, $ssl = false) {
			$ssl          = !empty ($ssl) ? strtolower ($ssl) : false;
			$hostName     = $ssl == 'ssl' ? "ssl://{$hostName}" : $hostName;
			$port         = $this->fixEmptyPort ($port, $ssl);
			$this->socket = $this->createSocket ($hostName, $port, $ssl);
			if (!$this->assumedNextLine ('* OK')) {
				throw new RuntimeException ('host doesn\'t allow connection');
			}
			$this->startTls ($ssl == 'tls');
		}

		/**
		 * @param string $accountHolder
		 * @param integer $startUid
		 * @param integer $maximumMessages
		 * @param string $folderName
		 * @param string $mask
		 *
		 * @return null|\Platzilla\MailManager\Message\Message[]
		 * @throws Exception
		 */
		public function fetchMessages ($accountHolder, $startUid, $maximumMessages, $folderName, $mask = null) {
			$this->select ($folderName);
			$messageUids = $this->fetchMessageUids ();
			if (empty ($messageUids)) {
				return null;
			}
			$mask = (!empty($mask)) ? "/\b{$mask}\b/i" : null;
			$totalFetchedMesages = 0;
			$parser              = new PhpMimeMailParser ();
			$messages            = array ();
			foreach ($messageUids as $messageUid) {
				if (($maximumMessages > 0) && ($totalFetchedMesages >= $maximumMessages)) {
					break;
				}

				if ($messageUid > $startUid) {
					$rawContents = ($this->fetchMessageRawContents ($messageUid));
					$parser->setText ($rawContents);
					$from = $this->getHeader ($parser, 'from');
					$to   = $this->getHeader ($parser, 'to');
					$cc   = $this->getHeader ($parser, 'cc');
					if ((!empty ($mask)) && (!preg_match ($mask, $to))) {
						continue;
					}

					$htmlMessageBody = $parser->getMessageBody ('htmlEmbedded');
					$message         = Message::getInstance ($accountHolder)
						->setBcc ($this->getHeader ($parser, 'bcc'))
						->setBody ($htmlMessageBody ? $htmlMessageBody : $parser->getMessageBody ())
						->setCc ($cc)
						->setDate (date_create ($parser->getHeader ('date')))
						->setFolderName ($folderName)
						->setFrom ($from)
						->setSubject ($this->getHeader ($parser, 'subject'))
						->setTo ($to)
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
		 * @param string $user
		 * @param string $password
		 *
		 * @return boolean
		 */
		public function login ($user, $password) {
			return $this->isOauth ? $this->sendOauth2Authentication ($user, $password) : parent::login ($user, $password);
		}

		/**
		 * @param string $accountHolder
		 * @param string $folderName
		 * @param Message $message
		 */
		public function saveSentMessage ($accountHolder, $folderName, $message) {
			if (!($message instanceof Message)) {
				return;
			}

			try {
				$to          = $this->normalizeRecipients ($message->getTo ());
				$cc          = $this->normalizeRecipients ($message->getCc ());
				$bcc         = $this->normalizeRecipients ($message->getBcc ());
				$zendMessage = new \Zend\Mail\Message ();
				$zendMessage->addFrom ($accountHolder)
					->setSubject ($message->getSubject ())
					->setBody ($message->getBody ());
				if (!empty ($to)) {
					$zendMessage->addTo ($to);
				}
				if (!empty ($cc)) {
					$zendMessage->addCc ($cc);
				}
				if (!empty ($bcc)) {
					$zendMessage->addBcc ($bcc);
				}

				$this->append ($folderName, $zendMessage->toString (), array (Storage::FLAG_SEEN));
			} catch (\Exception $ignored) {
				// Ignorar si hay algún problema, no es necesario explotar si no se puede guardar el mensaje enviado
			}
		}

	}
