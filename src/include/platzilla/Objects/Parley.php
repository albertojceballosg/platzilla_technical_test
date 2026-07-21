<?php
	require_once ('include/platzilla/Exceptions/ParleyException.php');

	class Parley {
		/** @var integer */
		private $id;

		/** @var DateTime */
		private $dateCreated;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $name;

		/** @var integer */
		private $recordId;

		/** @var integer */
		private $sourceRecord;

		/** @var integer */
		private $time;

		/** @var string */
		private $title;

		/** @var integer */
		private $usersId;

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return DateTime
		 */
		public function getDateCreated () {
			return $this->dateCreated;
		}

		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return integer
		 */
		public function getRecordId () {
			return $this->recordId;
		}

		/**
		 * @return integer
		 */
		public function getSourceRecord () {
			return $this->sourceRecord;
		}

		/**
		 * @return integer
		 */
		public function getTime () {
			return $this->time;
		}

		/**
		 * @return string
		 */
		public function getTitle () {
			return $this->title;
		}

		/**
		 * @return integer
		 */
		public function getUsersId () {
			return $this->usersId;
		}

		/**
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * @param $codParley
		 *
		 * @return Parley
		 */
		public function setId ($codParley) {
			if ((is_numeric ($codParley)) && ($codParley > 0) && (intval ($codParley) == $codParley)) {
				$this->id = $codParley;
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param $date
		 *
		 * @return Parley
		 */
		public function setDateCreated ($date) {
			$this->dateCreated = $date;
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return Parley
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
		 * @param string $moduleName
		 *
		 * @return Parley
		 */
		public function setModuleName ($moduleName) {
			if (is_scalar ($moduleName)) {
				$this->moduleName = $moduleName;
			} else {
				$this->moduleName = null;
			}
			return $this;
		}

		/**
		 * @param $name
		 *
		 * @return Parley
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param integer $record
		 *
		 * @return Parley
		 */
		public function setRecordId ($record) {
			if ((is_numeric ($record)) && ($record > 0) && (intval ($record) == $record)) {
				$this->recordId = $record;
			} else {
				$this->recordId = null;
			}
			return $this;
		}

		/**
		 * @param integer $record
		 *
		 * @return Parley
		 */
		public function setSourceRecord ($record) {
			if ((is_numeric ($record)) && ($record > 0) && (intval ($record) == $record)) {
				$this->sourceRecord = $record;
			} else {
				$this->sourceRecord = null;
			}
			return $this;
		}

		/**
		 * @param $ptime
		 *
		 * @return Parley
		 */
		public function setTime ($ptime) {
			$this->time = $ptime;
			return $this;
		}

		/**
		 * @param $title
		 *
		 * @return Parley
		 */
		public function setTitle ($title) {
			$this->title = $title;
			return $this;
		}

		/**
		 * @param $codUser
		 *
		 * @return Parley
		 */
		public function setUsersId ($codUser) {
			if ((is_numeric ($codUser)) && ($codUser > 0) && (intval ($codUser) == $codUser)) {
				$this->usersId = $codUser;
			} else {
				$this->usersId = null;
			}
			return $this;
		}

		/**
		 * @param Parley $chat
		 */
		public function copyValuesFrom ($chat) {
			if ((empty ($chat)) || (!($chat instanceof Parley))) {
				return;
			}
			$this->title        = $chat->getTitle ();
			$this->name         = $chat->getName ();
			$this->moduleName   = $chat->getModuleName ();
			$this->recordId     = $chat->getRecordId ();
			$this->sourceRecord = $chat->getSourceRecord ();
			$this->dateCreated  = $chat->getDateCreated ();
			$this->time         = $chat->getTime ();
			$this->usersId      = $chat->getUsersId ();
			$this->locked       = $chat->isLocked ();
		}

		/**
		 * @return Parley
		 * @throws ParleyException
		 */
		public function duplicate () {
			$object = new self ();
			return $object->setTitle ($this->title)
				->setName ($this->name)
				->setModuleName ($this->moduleName)
				->setRecordId ($this->recordId)
				->setSourceRecord ($this->sourceRecord)
				->setDateCreated ($this->dateCreated)
				->setTime ($this->time)
				->setUsersId ($this->usersId)
				->setLocked ($this->locked);
		}

		/**
		 * @param Parley $chat
		 *
		 * @return boolean
		 */
		public function isEqualTo ($chat) {
			if (
				(empty ($chat)) ||
				(!($chat instanceof Parley)) ||
				($this->title != $chat->getTitle ()) ||
				($this->name != $chat->getName ()) ||
				($this->moduleName != $chat->getModuleName ()) ||
				($this->recordId != $chat->getRecordId ()) ||
				($this->sourceRecord != $chat->getSourceRecord ()) ||
				($this->dateCreated != $chat->getDateCreated ()) ||
				($this->time != $chat->getTime ()) ||
				($this->usersId != $chat->getUsersId ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @return Parley
		 */
		public static function getInstance () {
			return new self ();
		}

	}
