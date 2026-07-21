<?php
	namespace Platzilla\MailManager\Message;

	class Message {
		/** @var string */
		private $accountHolder;

		/** @var Attachment[] */
		private $attachments;

		/** @var string */
		private $bcc;

		/** @var string */
		private $body;

		/** @var string */
		private $cc;

		/** @var \DateTime */
		private $date;

		/** @var string */
		private $folderName;

		/** @var string */
		private $from;

		/** @var string */
		private $subject;

		/** @var string */
		private $to;

		/** @var integer */
		private $uid;

		/**
		 * @param string $accountHolder
		 *
		 * @throws MessageException
		 */
		public function __construct ($accountHolder) {
			if ((empty ($accountHolder)) || (filter_var ($accountHolder, FILTER_VALIDATE_EMAIL) === false)) {
				throw new MessageException (MessageException::INVALID_ACCOUNT_HOLDER_EMAIL_ADDRESS);
			}

			$this->accountHolder = $accountHolder;
			$this->from          = $accountHolder;
		}

		// Getters and Setters

		/**
		 * @return string
		 */
		public function getAccountHolder () {
			return $this->accountHolder;
		}

		/**
		 * @return Attachment[]
		 */
		public function getAttachments () {
			return $this->attachments;
		}

		/**
		 * @return string
		 */
		public function getBcc () {
			return $this->bcc;
		}

		/**
		 * @return string
		 */
		public function getBody () {
			return $this->body;
		}

		/**
		 * @return string
		 */
		public function getCc () {
			return $this->cc;
		}

		/**
		 * @return \DateTime
		 */
		public function getDate () {
			return $this->date;
		}

		/**
		 * @return string
		 */
		public function getFolderName () {
			return $this->folderName;
		}

		/**
		 * @return string
		 */
		public function getFrom () {
			return $this->from;
		}

		/**
		 * @return string
		 */
		public function getSubject () {
			return $this->subject;
		}

		/**
		 * @return string
		 */
		public function getTo () {
			return $this->to;
		}

		/**
		 * @return integer
		 */
		public function getUid () {
			return $this->uid;
		}

		/**
		 * @param Attachment[] $attachments
		 *
		 * @return Message
		 */
		public function setAttachments ($attachments) {
			$this->attachments = $attachments;
			return $this;
		}

		/**
		 * @param string $bcc
		 *
		 * @return Message
		 */
		public function setBcc ($bcc) {
			$this->bcc = $bcc;
			return $this;
		}

		/**
		 * @param string $body
		 *
		 * @return Message
		 */
		public function setBody ($body) {
			$this->body = $body;
			return $this;
		}

		/**
		 * @param string $cc
		 *
		 * @return Message
		 */
		public function setCc ($cc) {
			$this->cc = $cc;
			return $this;
		}

		/**
		 * @param \DateTime $date
		 *
		 * @return Message
		 */
		public function setDate ($date) {
			$this->date = $date;
			return $this;
		}

		/**
		 * @param string $folderName
		 *
		 * @return Message
		 */
		public function setFolderName ($folderName) {
			$this->folderName = $folderName;
			return $this;
		}

		/**
		 * @param string $from
		 *
		 * @return Message
		 */
		public function setFrom ($from) {
			$this->from = $from;
			return $this;
		}

		/**
		 * @param string $subject
		 *
		 * @return Message
		 */
		public function setSubject ($subject) {
			$this->subject = $subject;
			return $this;
		}

		/**
		 * @param string $to
		 *
		 * @return Message
		 */
		public function setTo ($to) {
			$this->to = $to;
			return $this;
		}

		/**
		 * @param integer $uid
		 *
		 * @return Message
		 */
		public function setUid ($uid) {
			$this->uid = $uid;
			return $this;
		}

		// Other methods

		/**
		 * @return Message
		 */
		public function addAccountHolderToBcc () {
			$accountHolder = explode (' ', $this->accountHolder);
			$emailAddress  = $accountHolder [ (count ($accountHolder) - 1) ];

			$recipients = $this->getValidRecipients ();
			if (empty ($recipients)) {
				$found = false;
			} else {
				$found = false;
				foreach ($recipients as $recipient) {
					if (strstr ($recipient, $emailAddress) !== false) {
						$found = true;
						break;
					}
				}
			}

			if (!$found) {
				$bcc       = explode (',', $this->bcc);
				$bcc []    = $this->accountHolder;
				$this->bcc = join (',', array_filter ($bcc));
			}

			return $this;
		}

		/**
		 * @return string[]|null
		 */
		public function getValidRecipients () {
			$validRecipients = array_filter (
				array_merge (
					!empty ($this->to) ? explode (',', $this->to) : array (),
					!empty ($this->cc) ? explode (',', $this->cc) : array (),
					!empty ($this->bcc) ? explode (',', $this->bcc) : array ()
				)
			);
			return count ($validRecipients) > 0 ? $validRecipients : null;
		}

		/**
		 * @throws MessageException
		 */
		public function validate () {
			$validRecipients = $this->getValidRecipients ();
			if (empty ($validRecipients)) {
				throw new MessageException (MessageException::INVALID_RECIPIENTS);
			} else if (!($this->date instanceof \DateTime)) {
				throw new MessageException (MessageException::INVALID_DATE);
			} else if (empty ($this->from)) {
				throw new MessageException (MessageException::INVALID_FROM);
			} else if ((!empty ($this->uid)) && ((!is_numeric ($this->uid)) || ($this->uid <= 0))) {
				throw new MessageException (MessageException::INVALID_UID);
			}
		}

		/**
		 * @param string $accountHolder
		 *
		 * @return Message
		 */
		public static function getInstance ($accountHolder) {
			return new self ($accountHolder);
		}

	}
