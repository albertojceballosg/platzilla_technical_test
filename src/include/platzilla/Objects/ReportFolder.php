<?php
	require_once ('include/platzilla/Exceptions/ReportException.php');
	require_once ('include/platzilla/Objects/ReportInterface.php');

	class ReportFolder implements ReportInterface {
		/** @var integer */
		private $id;

		/** @var string */
		private $description;

		/** @var string */
		private $name;

		/** @var boolean */
		private $protected;

		/** @var string */
		private $status;

		/**
		 * ReportFolder constructor.
		 */
		public function __construct () {
			$this->protected = false;
			$this->status = self::STATUS_SAVED;
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * @return boolean
		 */
		public function isProtected () {
			return $this->protected;
		}

		/**
		 * @param integer $id
		 *
		 * @return ReportFolder
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return ReportFolder
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return ReportFolder
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param boolean $protected
		 *
		 * @return ReportFolder
		 */
		public function setProtected ($protected) {
			if (is_bool ($protected)) {
				$this->protected = $protected;
			}
			return $this;
		}

		/**
		 * @param string $status
		 *
		 * @return ReportFolder
		 */
		public function setStatus ($status) {
			if (in_array ($status, array (self::STATUS_CUSTOMIZED, self::STATUS_SAVED))) {
				$this->status = $status;
			}
			return $this;
		}

		/**
		 * @param ReportFolder $folder
		 */
		public function copyValuesFrom ($folder) {
			if ((empty ($folder)) || (!($folder instanceof ReportFolder))) {
				return;
			}

			$this->description = $folder->getDescription ();
			$this->name        = $folder->getName ();
			$this->protected   = $folder->isProtected ();
			$this->status      = $folder->getStatus ();
		}

		/**
		 * @param integer $newFolderId
		 *
		 * @return ReportFolder
		 * @throws ReportException
		 */
		public function duplicate ($newFolderId = null) {
			$this->validate ();

			$object = new self ();
			return $object->setId ($newFolderId)
				->setDescription ($this->description)
				->setName ($this->name)
				->setProtected ($this->protected)
				->setStatus ($this->status);
		}

		/**
		 * @param ReportFolder $folder
		 *
		 * @return boolean
		 */
		public function isEqualTo ($folder) {
			if (
				(empty ($folder)) ||
				(!($folder instanceof ReportFolder)) ||
				($this->description != $folder->getDescription ()) ||
				($this->name != $folder->getName ()) ||
				($this->protected != $folder->isProtected ()) ||
				($this->status != $folder->getStatus ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws ReportException
		 */
		public function validate () {
			if (empty ($this->name)) {
				throw new ReportException (ReportException::ERROR_REPORT_FOLDER_EMPTY_NAME);
			}
		}

		/**
		 * @return ReportFolder
		 */
		public static function getInstance () {
			return new self ();
		}

	}
