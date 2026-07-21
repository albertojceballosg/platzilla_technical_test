<?php
	require_once ('include/platzilla/Exceptions/ParleyException.php');

	class ParleyToUsers {
		/** @var integer */
		private $parleyid;

		/** @var integer */
		private $usersid;

		/** @var string */
		private $usersname;

		/** @var string */
		private $usersfrom;

		/** @var integer */
		private $jointime;

		/** @var DateTime */
		private $datecreated;

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
		public function getUsersId () {
			return $this->usersid;
		}

		/**
		 * @return string
		 */
		public function getUsersName () {
			return $this->usersname;
		}

		/**
		 * @return string
		 */
		public function getUsersFrom () {
			return $this->usersfrom;
		}

		/**
		 * @return integer
		 */
		public function getJoinTime () {
			return $this->jointime;
		}

		/**
		 * @return DateTime
		 */
		public function getDateCreated () {
			return $this->datecreated;
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
		 * @return ParleyToUsers
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
		 * @param integer $codUser
		 *
		 * @return ParleyToUsers
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
		 * @param $name
		 *
		 * @return ParleyToUsers
		 */
		public function setUsersName ($name) {
			$this->usersname = $name;
			return $this;
		}

		/**
		 * @param $idFrom
		 *
		 * @return ParleyToUsers
		 */
		public function setUsersFrom ($idFrom) {
			if ((is_numeric ($idFrom)) && ($idFrom > 0) && (intval ($idFrom) == $idFrom)) {
				$this->usersfrom = $idFrom;
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param $time
		 *
		 * @return ParleyToUsers
		 */
		public function setJoinTime ($time) {
			$this->jointime = $time;
			return $this;
		}

		/**
		 * @param $date
		 *
		 * @return ParleyToUsers
		 */
		public function setDateCreated ($date) {
			$this->datecreated = $date;
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return ParleyToUsers
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
		 * @param ParleyToUsers $chat
		 */
		public function copyValuesFrom ($chat) {
			if ((empty ($chat)) || (!($chat instanceof ParleyToUsers))) {
				return;
			}
			$this->parleyid    = $chat->getParleyId ();
			$this->usersid     = $chat->getUsersId ();
			$this->usersname   = $chat->getUsersName ();
			$this->usersfrom   = $chat->getUsersFrom ();
			$this->jointime    = $chat->getJoinTime ();
			$this->datecreated = $chat->getDateCreated ();
			$this->locked      = $chat->isLocked ();
		}

		/**
		 * @return ParleyToUsers
		 */
		public function duplicate () {
			$object = new self ();
			return $object->setParleyId ($this->parleyid)
				->setUsersId ($this->usersid)
				->setUsersName ($this->usersname)
				->setUsersFrom ($this->usersfrom)
				->setJoinTime ($this->jointime)
				->setDateCreated ($this->datecreated)
				->setLocked ($this->locked);
		}

		/**
		 * @param ParleyToUsers $chat
		 *
		 * @return boolean
		 */
		public function isEqualTo ($chat) {
			if (
				(empty ($chat)) ||
				(!($chat instanceof ParleyToUsers)) ||
				($this->parleyid != $chat->getParleyId ()) ||
				($this->usersname != $chat->getUsersName ()) ||
				($this->usersfrom != $chat->getUsersFrom ()) ||
				($this->jointime != $chat->getJoinTime ()) ||
				($this->datecreated != $chat->getDateCreated ()) ||
				($this->usersid != $chat->getUsersId ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @return ParleyToUsers
		 */
		public static function getInstance () {
			return new self ();
		}

	}
