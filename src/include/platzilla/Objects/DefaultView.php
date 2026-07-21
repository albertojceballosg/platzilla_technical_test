<?php
	require_once ('include/platzilla/Exceptions/HowToUseException.php');
	require_once ('include/platzilla/Objects/HowToUseInterface.php');
	require_once ('include/platzilla/Objects/MasterView.php');

	/**
	 * Class DefaultView
	 */
	class DefaultView implements HowToUseInterface {

		/** @var integer */
		private $howUseId;

		/** @var string */
		private $howUseName;

		/** @var integer */
		private $id;

		/** @var MasterView */
		private $masterView;

		/** @var string */
		private $moduleName;

		/** @var integer */
		private $userId;

		/**
		 * @param MasterView $masterView
		 *
		 * @return boolean
		 */
		private function isEqualMasterView ($masterView) {
			if (
				(!empty ($masterView) && empty ($this->masterView)) ||
				(empty ($masterView) && !empty ($this->masterView))
			) {
				return false;
			} else if ($this->masterView->isEqualTo ($masterView)) {
				return true;
			}
			return false;
		}

		/**
		 * @param DefaultView $defaultView
		 */
		public function copyValuesFrom ($defaultView) {
			if (empty($defaultView) || !$defaultView instanceof DefaultView) {
				return;
			}
			$this->id         = $defaultView->getId();
			$this->howUseId   = $this->getHowUseId ();
			$this->howUseName = $this->getHowUseName ();
			$this->masterView = $defaultView->getMasterView ();
			$this->moduleName = $defaultView->getMasterView ();
			$this->userId     = $defaultView->getHowUseId ();
		}

		/**
		 * @param null|integer $defaultViewId
		 *
		 * @return DefaultView
		 */
		public function duplicate ($defaultViewId = null) {
			$object = new self ();
			return $object->setId ((empty ($defaultViewId)) ? $this->id : $defaultViewId)
				->setHowUseName ($this->howUseName)
				->setHowUseId ($this->getHowUseId())
				->setMasterView ($this->masterView)
				->setModuleName ($this->moduleName)
				->setUserId ($this->userId);
		}

		/**
		 * @return integer
		 */
		public function getHowUseId () {
			return $this->howUseId;
		}

		/**
		 * @return string
		 */
		public function getHowUseName () {
			return $this->howUseName;
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return MasterView
		 */
		public function getMasterView () {
			return $this->masterView;
		}

		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return integer
		 */
		public function getUserId () {
			return $this->userId;
		}

		/**
		 * @param DefaultView $defaultView
		 *
		 * @return boolean
		 */
		public function isEqualTo ($defaultView) {
			if (
				empty ($defaultView) ||
				($this->howUseName != $defaultView->getHowUseName ()) ||
				(!$this->isEqualMasterView ($defaultView->getMasterView ())) ||
				($this->moduleName != $defaultView->getModuleName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @param integer $howUseId
		 *
		 * @return DefaultView
		 */
		public function setHowUseId ($howUseId) {
			$this->howUseId = $howUseId;
			return $this;
		}

		/**
		 * @param string $howUseName
		 *
		 * @return DefaultView
		 */
		public function setHowUseName ($howUseName) {
			$this->howUseName = $howUseName;
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return DefaultView
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param MasterView $masterView
		 *
		 * @return DefaultView
		 */
		public function setMasterView ($masterView) {
			$this->masterView = $masterView;
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return DefaultView
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param integer $userId
		 *
		 * @return DefaultView
		 */
		public function setUserId ($userId) {
			$this->userId = $userId;
			return $this;
		}

		/**
		 * @throws HowToUseException
		 */
		public function validate () {
			if (empty($this->moduleName)) {
				throw new HowToUseException(HowToUseException::ERROR_VIEW_EMPTY_DEFAULT_VIEW_MODULE);
			} else if (empty($this->masterView)) {
				throw new HowToUseException(HowToUseException::ERROR_VIEW_EMPTY_MASTER_VIEW);
			}
		}

		/**
		 * @return DefaultView
		 */
		public static function getInstance () {
			return new self ();
		}

	}
