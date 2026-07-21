<?php
	require_once ('include/platzilla/Objects/HowToUseInterface.php');

	/**
	 * Class MasterView
	 */
	class MasterView implements HowToUseInterface {

		/** @var integer */
		private $id;

		/** @var string */
		private $name;

		/** @var string */
		private $tabView;

		/** @var string */
		private $viewName;

		/**
		 * @param MasterView $masterView
		 */
		public function copyValuesFrom ($masterView) {
			if (empty ($masterView) || !$masterView instanceof MasterView) {
				return;
			}
			$this->id       = $masterView->getId ();
			$this->name     = $masterView->getName ();
			$this->tabView  = $masterView->getTabView ();
			$this->viewName = $masterView->getViewName ();
		}

		/**
		 * @param null|integer $masterViewId
		 *
		 * @return MasterView
		 */
		public function duplicate ($masterViewId = null) {
			$object = new self ();
			return $object->setId((empty($masterViewId)) ? $this->id : $masterViewId)
				->setName ($this->name)
				->setTabView ($this->tabView)
				->setViewName ($this->viewName);
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
		public function getName () {
			return $this->name;
		}

		/**
		 * @return string
		 */
		public function getTabView () {
			return $this->tabView;
		}

		/**
		 * @return string
		 */
		public function getViewName () {
			return $this->viewName;
		}

		/**
		 * @param MasterView $masterView
		 *
		 * @return boolean
		 */
		public function isEqualTo ($masterView) {
			if (
				empty($masterView) ||
				($this->name != $masterView->getName ()) ||
				($this->tabView !== $masterView->getTabView ()) ||
				($this->viewName !== $masterView->getViewName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @param integer $id
		 *
		 * @return MasterView
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return MasterView
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param string $tabView
		 *
		 * @return MasterView
		 */
		public function setTabView ($tabView) {
			$this->tabView = $tabView;
			return $this;
		}

		/**
		 * @param string $viewName
		 *
		 * @return MasterView
		 */
		public function setViewName ($viewName) {
			$this->viewName = $viewName;
			return $this;
		}

		/**
		 * @return MasterView
		 */
		public static function getInstance () {
			return new self ();
		}

	}
