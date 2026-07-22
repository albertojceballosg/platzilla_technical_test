<?php
	require_once ('include/platzilla/Data/EntityCommentsException.php');
	require_once ('include/platzilla/Data/EntityCommentsInterface.php');

	class EntityComments implements EntityCommentsInterface {

		/** @var integer */
		private $commentId;

		/** @var integer */
		private $crmId;

		/** @var string */
		private $commentType;

		/** @var string */
		private $statement;

		/** @var string */
		private $userAvatar;

		/** @var string */
		private $userName;

		/** @var integer */
		private $writtenBy;

		/** @var datetime */
		private $writtenOn;

		/**
		 * @return integer
		 */
		public function getCommentId () {
			return $this->commentId;
		}

		/**
		 * @return integer
		 */
		public function getCrmId () {
			return $this->crmId;
		}

		/**
		 * @return string
		 */
		public function getCommentType () {
			return $this->commentType;
		}

		/**
		 * @return string
		 */
		public function getStatement () {
			return $this->statement;
		}

		/**
		 * @return string
		 */
		public function getUserAvatar () {
			return $this->userAvatar;
		}

		/**
		 * @return string
		 */
		public function getUserName () {
			return $this->userName;
		}

		/**
		 * @return integer
		 */
		public function getWrittenBy () {
			return $this->writtenBy;
		}

		/**
		 * @return datetime
		 */
		public function getWrittenOn () {
			return $this->writtenOn;
		}

		/**
		 * @param integer $commentId
		 *
		 * @return EntityComments
		 */
		public function setCommentId ($commentId) {
			$this->commentId = $commentId;
			return $this;
		}

		/**
		 * @param integer $crmId
		 *
		 * @return EntityComments
		 */
		public function setCrmId ($crmId) {
			$this->crmId = $crmId;
			return $this;
		}

		/**
		 * @param string $commentType
		 *
		 * @return EntityComments
		 */
		public function setCommentType ($commentType) {
			$types = array_keys(self::COMMENT_TYPE);
			if (in_array($commentType, $types)) {
				$this->commentType = $commentType;
			} else {
				$this->commentType = $types [1];
			}
			return $this;
		}

		/**
		 * @param string $statement
		 *
		 * @return EntityComments
		 */
		public function setStatement ($statement) {
			$this->statement = $statement;
			return $this;
		}

		/**
		 * @param string $userAvatar
		 *
		 * @return EntityComments
		 */
		public function setUserAvatar ($userAvatar) {
			if (!empty ($userAvatar)) {
				$this->userAvatar = $userAvatar;
			} else {
				$this->userAvatar = self::GENERAL_AVATAR;
			}
			return $this;
		}

		/**
		 * @param string $userName
		 *
		 * @return EntityComments
		 */
		public function setUserName ($userName) {
			$this->userName = $userName;
			return $this;
		}

		/**
		 * @param integer $writtenBy
		 *
		 * @return EntityComments
		 */
		public function setWrittenBy ($writtenBy) {
			$this->writtenBy = $writtenBy;
			return $this;
		}

		/**
		 * @param datetime $writtenOn
		 *
		 * @return EntityComments
		 */
		public function setWrittenOn ($writtenOn) {
			if (!empty ($writtenOn)) {
				setlocale (LC_ALL, 'es_ES', 'Spanish_Traditional_Sort',  'Spanish_Spain', 'Spanish');
				$this->writtenOn = ucwords(mb_convert_encoding(strftime ('%A %d, %B - %Y', strtotime ($writtenOn)), 'UTF-8', 'ISO-8859-1'));
			} else {
				$this->writtenOn = date_create ()->format ('Y-m-d H:i:s');
			}
			return $this;
		}

		/**
		 * @throws EntityCommentsException
		 */
		public function validate () {
			if (empty ($this->statement)) {
				throw new EntityCommentsException(EntityCommentsException::ERROR_CREATE_EMPTY_NOTE);
			} else if (empty($this->commentType)) {
				throw new EntityCommentsException(EntityCommentsException::ERROR_CREATE_EMPTY_NOTE_TYPE);
			}
		}

		/**
		 * @return EntityComments
		 */
		public static function getInstance () {
			return new self ();
		}

	}
