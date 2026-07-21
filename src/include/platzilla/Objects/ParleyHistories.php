<?php
	require_once ('include/platzilla/Exceptions/ParleyException.php');

	class ParleyHistories {
		/** @var integer */
		private $parleyid;

		/** @var integer */
		private $messagetime;

		/** @var string */
		private $message;

		/** @var string */
		private $attached;

		/** @var integer */
		private $usersid;

		/** @var string */
		private $usersavatar;

		/** @var boolean */
		private $status;

		/** @var boolean */
		private $locked;

		/**
		 * @return integer
		 */
		public function getParleyId () {
			return $this->parleyid;
		}

		/**
		 * @return integer
		 */
		public function getMessageTime () {
			return $this->messagetime;
		}

		/**
		 * @return string
		 */
		public function getMessage () {
			return $this->message;
		}

		/**
		 * @return string
		 */
		public function getAttached () {
			return $this->attached;
		}

		/**
		 * @return integer
		 */
		public function getUsersId () {
			return $this->usersid;
		}

		/**
		 * @return string
		 */
		public function getUsersAvatar () {
			return $this->usersavatar;
		}

		/**
		 * @return boolean
		 */
		public function getParleyStatus () {
			return $this->status;
		}

		/**
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * @param integer $codParley
		 *
		 * @return ParleyHistories
		 */
		public function setParleyId ($codParley) {
			if ((is_numeric ($codParley)) && ($codParley > 0) && (intval ($codParley) == $codParley)) {
				$this->parleyid = $codParley;
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param $parleyTime
		 *
		 * @return ParleyHistories
		 */
		public function setMessageTime ($parleyTime) {
			$this->messagetime = $parleyTime;
			return $this;
		}

		/**
		 * @param $message
		 *
		 * @return ParleyHistories
		 */
		public function setMessage ($message) {
			$this->message = $message;
			return $this;
		}

		/**
		 * @param $attached
		 *
		 * @return ParleyHistories
		 */
		public function setAttached ($attached) {
			$this->attached = $attached;
			return $this;
		}

		/**
		 * @param $avatar
		 *
		 * @return ParleyHistories
		 */
		public function setUsersAvatar ($avatar) {
			$this->usersavatar = $avatar;
			return $this;
		}

		/**
		 * @param $status
		 *
		 * @return ParleyHistories
		 */
		public function setParleyStatus ($status) {
			$this->status = $status;
			return $this;
		}

		/**
		 * @param $codUser
		 *
		 * @return ParleyHistories
		 */
		public function setUsersId ($codUser) {
			if ((is_numeric ($codUser)) && ($codUser > 0) && (intval ($codUser) == $codUser)) {
				$this->usersid = $codUser;
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return ParleyHistories
		 */
		public function setLocked ($locked) {
			if ($locked) {
				$this->locked = 1;
			} else {
				$this->locked = 0;
			}
			return $this;
		}

		/**
		 * @param ParleyHistories $chat
		 */
		public function copyValuesFrom ($chat) {
			if ((empty ($chat)) || (!($chat instanceof ParleyHistories))) {
				return;
			}
			$this->parleyid    = $chat->getParleyId ();
			$this->messagetime = $chat->getMessageTime ();
			$this->message     = $chat->getMessage ();
			$this->attached    = $chat->getAttached ();
			$this->usersavatar = $chat->getUsersAvatar ();
			$this->status      = $chat->getParleyStatus ();
			$this->usersid     = $chat->getUsersId ();
			$this->status      = $chat->getParleyStatus ();
			$this->locked      = $chat->isLocked ();
		}

		/**
		 * @return ParleyHistories
		 * @throws ParleyException
		 */
		public function duplicate () {
			$object = new self ();
			return $object->setParleyId ($this->parleyid)
				->setMessageTime ($this->messagetime)
				->setMessage ($this->message)
				->setAttached ($this->attached)
				->setUsersAvatar ($this->usersavatar)
				->setParleyStatus ($this->status)
				->setUsersId ($this->usersid)
				->setLocked ($this->locked);
		}

		/**
		 * @param ParleyHistories $chat
		 *
		 * @return boolean
		 */
		public function isEqualTo ($chat) {
			if (
				(empty ($chat)) ||
				(!($chat instanceof ParleyHistories)) ||
				($this->parleyid != $chat->getParleyId ()) ||
				($this->messagetime != $chat->getMessageTime ()) ||
				($this->message != $chat->getMessage ()) ||
				($this->attached != $chat->getAttached ()) ||
				($this->usersavatar != $chat->getUsersAvatar ()) ||
				($this->status != $chat->getParleyStatus ()) ||
				($this->usersid != $chat->getUsersId ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @return ParleyHistories
		 */
		public static function getInstance () {
			return new self ();
		}

	}
